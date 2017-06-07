<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHSystem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;

use CASHMusic\Core\CASHDBAL;

class EntityBase
{

    protected $db;
    protected $fillable = [];
    protected $query = null;

    public function __construct()
    {
        $this->db = CASHDBAL::entityManager();
    }

    /**
     * EntityBase constructor. Loads entity manager for Doctrine ORM.
     */

    /**
     * Static method shortcut to search by id.
     * @param $id
     * @param $limit
     * @param $order_by
     * @param $offset
     * @return object|bool
     */
    public static function find($id)
    {
        // if it's an array of ids we can try to get multiples
        try {
            $db = CASHDBAL::entityManager();
            $object = $db->getRepository(get_called_class())->findOneBy(['id'=>$id]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        if ($object) return $object;

        return false;
    }

    /**
     * Static method shortcut to search by multiple values.
     * $values is a key=>value pair array
     *
     * [
     * 'email_address' => "dev@cashmusic.org",
     * 'is_admin' => "1"
     * ]
     *
     * @param $values
     * @param $limit
     * @param $order_by
     * @param $offset
     * @return object|bool
     */
    public static function findWhere($values, $limit=null, $order_by=null, $offset=null)
    {

        try {
            $db = CASHDBAL::entityManager();

            // if it's an array of ids we can try to get multiples
            if (is_array($values)) {
                $object = $db->getRepository(get_called_class())->findBy($values);
            } else {
                $object = self::find($values);
            }

            if (is_array($object) && count($object) == 1) return $object[0];

            if ($object) return $object;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    /**
     * Static method shortcut to get all model results.
     * @param $limit
     * @param $order_by
     * @param $offset
     * @return object|bool
     */
    public static function all($limit=null, $order_by=null, $offset=null)
    {

        try {


            $db = CASHDBAL::entityManager();
            $object = $db->getRepository(get_called_class())->findAll();

            if ($object) return $object;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    /**
     * Static method shortcut to map an array of values to a model and save it.
     * @param $values
     * @return EntityBase
     * @throws \Exception
     */
    public static function create($values)
    {
        $entity_class = get_called_class();
        $new_object = new $entity_class();

        // map the array to the object; let the magic __set method figure out if it's allowed or not
        if (is_array($values) && count($values) > 0) {
            foreach ($values as $key => $value) {
                $new_object->$key = $value;
            }

            try {
                $db = CASHDBAL::entityManager();
                $db->persist($new_object);
                $db->flush();
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }

            return $new_object;
        } else {
            throw new \Exception("A valid set of values is required to create a CASHMusic\\Entity with the create method.");
        }
    }

    /**
     * Public shortcut function to save model to database with Doctrine mapping.
     * @return $this|boolean
     */
    public function save()
    {
        try {
            if (!$this->db) $this->db = CASHDBAL::entityManager();
            $this->db->merge($this);
            $this->db->flush();
        } catch (\Exception $e) {
            if (CASH_DEBUG) {
                CASHSystem::errorLog($e->getMessage());
            }

            return false;
        }

        return $this;
    }

    /**
     * Public shortcut method for mass updating model properties. Gets filtered through $fillable.
     * @param $values
     * @return $this
     * @throws \Exception
     */
    public function update($values) {
        if (is_array($values) && count($values) > 0) {
            foreach ($values as $key => $value) {
                $this->$key = $value;
            }

            $this->save();

            return $this;
        } else {
            throw new \Exception("A valid set of values is required to update a CASHMusic\\Entity with the update method.");
        }
    }

    /**
     * Public shortcut method to delete a model.
     * @param $id
     */
    public function delete($id) {

        try {
            $db = CASHDBAL::entityManager();

            $object = self::find($id);

            $db->remove($object);
            $db->flush();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }

    }

    /**
     * Magic getter method with custom override available in extender class, via get{PropertyName}Attribute
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        $custom_method = "get" . ucwords($property) . "Attribute";

        if (method_exists($this, $custom_method)) {
            return $this->$custom_method();
        } else if (property_exists($this, $property)) {
            return $this->$property;
        }

        return false;
    }

    /**
     * Magic setter method with custom override available in extender class, via set{PropertyName}Attribute. Checks attributes against public $fillable array. Anything not in the array is ignored.
     * @param $property
     * @param $value
     * @return bool
     */
    public function __set($property, $value)
    {

        // never let a property be set unless it's in $fillable array
        if (!in_array($property, $this->fillable)) return false;

        $custom_method = "set" . ucwords($property) . "Attribute";

        if (method_exists($this, $custom_method)) {
            return $this->$custom_method($value);
        } elseif (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    /*
     * Relationships
     */

    /**
     * @param $entity
     * @param bool $key
     * @param bool $foreign_key
     * @return array
     */
    public function hasOne($entity, $key=false, $foreign_key=false, $conditions=false) {
        return $this->getRelationship($entity, $key, $foreign_key, false, $conditions);
    }

    /**
     * @param $entity
     * @param bool $key
     * @param bool $foreign_key
     * @param bool $where
     * @param bool $limit
     * @param bool $order_by
     * @return array
     */
    public function hasMany($entity, $key=false, $foreign_key=false, $where=false, $limit=false, $order_by=false) {
        return $this->getRelationship($entity, $key, $foreign_key, false, [
            'where'=>$where,
            'limit'=>$limit,
            'order_by'=>$order_by
        ]);
    }

    public function hasManyPolymorphic($entity, $key, $scope_alias, $where=false, $limit=false, $order_by=false) {
        return $this->getRelationship($entity, $key, "polymorphic", $scope_alias, [
            'where'=>$where,
            'limit'=>$limit,
            'order_by'=>$order_by
        ]);
    }

    /**
     * @param $entity
     * @param bool $key
     * @param bool $foreign_key
     * @return array
     */
    public function belongsTo($entity, $key=false, $foreign_key=false, $conditions=false) {
        return $this->getRelationship($entity, $key, $foreign_key, false, $conditions);
    }

    /**
     * @param $entity
     * @param bool $key
     * @param bool $foreign_key
     * @return array
     * @throws \Exception
     */
    public function getRelationship($entity, $key=false, $foreign_key=false, $scope=false, $conditions=false) {

        try {
            $class_fqdn = "\\CASHMusic\\Entities\\$entity";
            $db = CASHDBAL::entityManager();
            $tableName = $db->getClassMetadata($class_fqdn)->getTableName();

            if (!$key) {
                $key = $this->id;
            } else {
                $key = $this->$key;
            }

            if (!$foreign_key) {
                if (substr($tableName, -1) == 's') {
                    $foreign_key = substr($tableName, 0, -1) . "_id";
                } else {
                    throw new \Exception("Needs a foreign key name.");
                }
            }


            $query = CASHDBAL::queryBuilder();
            $query = $query->select($tableName)->from($class_fqdn, $tableName);

            // if this is non polymorphic
            if (!$scope) {
                $query = $query->where(
                    $query->expr()->eq($tableName . '.' . $foreign_key, ':key')
                )->setParameter(':key', $key);
            } else {
                $query = $query->where(
                    $query->expr()->eq($tableName . '.scope_table_id', ':key')
                )->andWhere(
                    $query->expr()->eq($tableName . '.scope_table_alias', ':scope')
                )->setParameter(':key', $key)->setParameter(':scope', $scope);
            }

            $result = $query->getQuery()->getResult(5);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }

        return $result;
    }

    /**
     * @return array
     */
    public function toArray() {

        $properties = get_object_vars($this);

        // make sure we're getting an array for these properties
        foreach ($properties as $key => &$property) {
            if (CASHSystem::isJson($property)) { //in_array($key, ['metadata', 'data'])
                $property = json_decode($property, true);
            }
        }

        unset($properties['fillable']);
        unset($properties['db']);
        unset($properties['query']);

        return $properties;
    }

    /**
     * @return string
     */
    public function toJson() {
        $properties = $this->toArray();

        return json_encode($properties);
    }

    public function dd() {
        $properties = $this->toArray();
        CASHSystem::dd($properties);
    }

    public function getFieldType($field) {

        try {
            $metadata = CASHDBAL::entityManager()->getClassMetadata(get_called_class());

            if (isset($metadata->fieldMappings[$field])) {
                $nameMetadata = $metadata->fieldMappings[$field];
                return $nameMetadata['type'];
            } else {
                return false;
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
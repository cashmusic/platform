<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHSystem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use CASHMusic\Core\CASHData;

use CASHMusic\Core\CASHDBAL;

class EntityBase extends CASHData
{

    protected $db;
    protected $orm;
    protected $fillable = [];
    protected $query = null;

    /**
     * EntityBase constructor. Loads entity manager for Doctrine ORM.
     */

    public function __construct()
    {
        if (!$this->orm) $this->connectDB();
    }

    /**
     * Static method shortcut to search by id.
     * @param $em
     * @param $id
     * @param $limit
     * @param $order_by
     * @param $offset
     * @return object|bool
     */
    public static function find($em, $id)
    {
        try {
            $object = $em->getRepository(get_called_class())->findOneBy(['id'=>$id]);
        } catch (\Exception $e) {
            CASHSystem::errorLog("Entity ".get_called_class()." Exception: ".$e->getMessage());
            CASHSystem::errorLog("Details_____");
            CASHSystem::errorLog($e->getTrace());
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
     * @param $em
     * @param $values
     * @param $limit
     * @param $order_by
     * @param $offset
     * @return object|bool
     */
    public static function findWhere($em, $values, $force_array=false, $order_by=null, $limit=null, $offset=null)
    {

        try {
            // if it's an array of ids we can try to get multiples
            if (is_array($values)) {
                $object = $em->getRepository(get_called_class())->findBy($values, $order_by, $limit, $offset);
            } else {
                $object = self::find($em, $values);
            }

            if (is_array($object)) {

                if (!$force_array) {
                    if (count($object) == 1) return $object[0];
                }

                if(count($object) < 1) return false;
            }

            return $object;
        } catch (\Exception $e) {
            CASHSystem::errorLog("Entity ".get_called_class()." Exception: ".$e->getMessage());
            CASHSystem::errorLog("Details_____");
            CASHSystem::errorLog($e->getTrace());
        }

        return false;
    }


    /**
     * Static method shortcut to get all model results.
     * @param $em
     * @param $limit
     * @param $order_by
     * @param $offset
     * @return object|bool
     */
    public static function all($em, $limit=null, $order_by=null, $offset=null)
    {

        try {
            $object = $em->getRepository(get_called_class())->findAll();

            if ($object) return $object;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    /**
     * Static method shortcut to map an array of values to a model and save it.
     * @param $em
     * @param $values
     * @return EntityBase
     * @throws \Exception
     */
    public static function create($em, $values)
    {
        $entity_class = get_called_class();
        $new_object = new $entity_class();

        // map the array to the object; let the magic __set method figure out if it's allowed or not
        if (is_array($values) && count($values) > 0) {
            foreach ($values as $key => $value) {
                $new_object->$key = $value;
            }

            try {
                $em->persist($new_object);
                $em->flush();
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
            if (!$this->orm) $this->connectDB();
            $this->orm->em->merge($this);
            $this->orm->em->flush();
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
    public function delete() {

        try {
            if (!$this->orm) $this->connectDB();

            $entity = $this->orm->em->merge($this);
            $this->orm->em->remove($entity);
            $this->orm->em->flush();

            return true;

        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }

    }

    /** @PrePersist */
    public function doOnPrePersist()
    {
        $this->creation_date = time();
        $this->modification_date = time();

        /*if (property_exists($this, "data")) {
            if (!isset($this->data)) $this->data = [];
        }*/
    }

    /** @PreUpdate */
    public function doOnPreUpdate()
    {
        $this->modification_date = time();

        /*if (property_exists($this, "data")) {
            if (!isset($this->data)) $this->data = [];
        }*/
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
            // this should hopefully take care of empty json_array field issue
            if ($this->getFieldType($property) == "json_array") {

                if (gettype($this->$property) == "string") {
                    if ($value = json_decode($this->$property, true)) {
                        return $value;
                    }

                    return array();
                }

                if (empty($this->$property)) return array();
            }
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
    public function hasOne($entity, $key=false, $foreign_key=false, $conditions=[]) {

        list($where, $limit, $order_by) = $this->mapRelationshipConditions($conditions);

        return $this->getRelationship($entity, $key, $foreign_key, false, [
            'where'=>$where,
            'limit'=>$limit,
            'order_by'=>$order_by
        ]);
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
    public function hasMany($entity, $key=false, $foreign_key=false, $conditions=[]) {

        list($where, $limit, $order_by) = $this->mapRelationshipConditions($conditions);

        return $this->getRelationship($entity, $key, $foreign_key, false, [
            'where'=>$where,
            'limit'=>$limit,
            'order_by'=>$order_by
        ]);
    }

    public function hasManyPolymorphic($entity, $key, $scope_alias, $conditions=[]) {

        list($where, $limit, $order_by) = $this->mapRelationshipConditions($conditions);

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
    public function belongsTo($entity, $key=false, $foreign_key=false, $conditions=[]) {
        return $this->getRelationship($entity, $key, $foreign_key, false, $conditions);
    }

    /**
     * @param $entity
     * @param bool $key
     * @param bool $foreign_key
     * @return array
     * @throws \Exception
     */
    public function getRelationship($entity, $key=false, $foreign_key=false, $scope=false, $conditions=[]) {

        try {
            $class_fqdn = "\\CASHMusic\\Entities\\$entity";
            if (!$this->orm) $this->connectDB();
            $tableName = $this->orm->em->getClassMetadata($class_fqdn)->getTableName();

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

            // if this is non polymorphic
            if (!$scope) {
                if (class_exists($class_fqdn)) {
                    // relationship conditions will ALWAYS override any redundant conditions passed
                    $where_conditions = $this->parseWhereConditions($conditions['where'], [$foreign_key => $key]);

                    $result = $class_fqdn::findWhere($this->orm->em, $where_conditions, true, $conditions['order_by'], $conditions['limit']);
                } else {
                    throw new \Exception("Entity class $class_fqdn does not exist.");
                }

            } else {

                if (class_exists($class_fqdn)) {
                    // relationship conditions will ALWAYS override any redundant conditions passed
                    $where_conditions = $this->parseWhereConditions($conditions['where'], ['scope_table_id' => $key, 'scope_table_alias'=>$scope]);

                    $result = $class_fqdn::findWhere($this->orm->em, $where_conditions, true, $conditions['order_by'], $conditions['limit']);
                } else {
                    throw new \Exception("Entity class $class_fqdn does not exist.");
                }
            }


        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }

        return $result;
    }

    public function mapRelationshipConditions($conditions) {
        $where = null;
        $limit = null;
        $order_by = null;

        if (isset($conditions['where'])) $where = $conditions['where'];
        if (isset($conditions['limit'])) $limit = $conditions['limit'];
        if (isset($conditions['order_by'])) $order_by = $conditions['order_by'];

        return [$where, $limit, $order_by];
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
            if (!$this->orm) $this->connectDB();
            $metadata = $this->orm->em->getClassMetadata(get_called_class());

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

    public function __isset($name){
        return isset($this->$name);
    }

    /**
     * @param $key
     * @param $foreign_key
     * @param $conditions
     * @return array
     */
    public function parseWhereConditions($where, $foreign_constraints)
    {
        if ($where) {
            return array_merge($where, $foreign_constraints);
        } else {
            return $foreign_constraints;
        }
    }

}
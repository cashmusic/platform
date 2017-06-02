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

    /**
     * EntityBase constructor. Loads entity manager for Doctrine ORM.
     */
    public function __construct()
    {
        $this->db = CASHDBAL::entityManager();
    }

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
        $db = CASHDBAL::entityManager();
        // if it's an array of ids we can try to get multiples
        try {
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

        $db = CASHDBAL::entityManager();

        // if it's an array of ids we can try to get multiples
        if (is_array($values)) {
            $object = $db->getRepository(get_called_class())->findBy($values);
        } else {
            $object = self::find($values);
        }

        if ($object) return $object;

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

        $db = CASHDBAL::entityManager();
        $object = $db->getRepository(get_called_class())->findAll();

        if ($object) return $object;

        return false;
    }

    /**
     * This is a start; we could use static method chaining to make this less shitty.
     * the getQuery/getResult combo at the end kills me.
     *
     * $user = People::query()
     * ->where("t.email_address = :email")
     * ->setParameter('email', 'info@cashmusic.org')
     * ->getQuery()->getResult();
     *
     * @return \Doctrine\ORM\QueryBuilder
     */

/*    public static function query()
    {
        $db = CASHDBAL::entityManager();
        return $db->getRepository(get_called_class())->createQueryBuilder("t");
    }*/

/*    public static function query()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
    }*/


    /**
     * Static method shortcut to map an array of values to a model and save it.
     * @param $values
     * @return EntityBase
     * @throws \Exception
     */
    public static function create($values)
    {

        $new_object = new self();

        // map the array to the object; let the magic __set method figure out if it's allowed or not
        if (is_array($values) && count($values) > 0) {
            foreach ($values as $key => $value) {
                $new_object->$key = $value;
            }

            $new_object->save();

            return $new_object;
        } else {
            throw new \Exception("A valid set of values is required to create a CASHMusic\\Entity with the create method.");
        }
    }

    /**
     * Public shortcut function to save model to database with Doctrine mapping.
     * @return $this
     */
    public function save()
    {
        if (!$this->db) $this->db = CASHDBAL::entityManager();
        $this->db->merge($this);
        $this->db->flush();

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

        $db = CASHDBAL::entityManager();

        $object = self::find($id);

        $db->remove($object);
        $db->flush();

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
        return $this->getRelationship($entity, $key, $foreign_key, $conditions);
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
        return $this->getRelationship($entity, $key, $foreign_key, [
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
        return $this->getRelationship($entity, $key, $foreign_key, $conditions);
    }

    /**
     * @param $entity
     * @param bool $key
     * @param bool $foreign_key
     * @return array
     * @throws \Exception
     */
    public function getRelationship($entity, $key=false, $foreign_key=false, $conditions=false) {

        $class_fqdn = "\\CASHMusic\\Entities\\$entity";
        $db = CASHDBAL::entityManager();
        $tableName = $db->getClassMetadata($class_fqdn)->getTableName();

        if (!$key) {
            $key = $this->id;
        } else {
            $key = CASHSystem::snakeToCamelCase($key);;
            $key = $this->$key;
        }

        if (!$foreign_key) {
            if (substr($tableName, -1) == 's')
            {
                $foreign_key = substr($tableName, 0, -1)."_id";
            } else {
                throw new \Exception("Needs a foreign key name.");
            }
        } else {
            $foreign_key = CASHSystem::snakeToCamelCase($foreign_key);
        }



        $query = CASHDBAL::queryBuilder();
        $query = $query->select($tableName)->from($class_fqdn, $tableName)->where(
            $query->expr()->eq($tableName.'.'.$foreign_key, ':key')
        )
        ->setParameter(':key', $key);

        $query->getQuery()->getResult(5);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray() {

        $properties = get_object_vars($this);

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
}
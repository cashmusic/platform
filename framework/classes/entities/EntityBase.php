<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHSystem;
use Doctrine\ORM\Mapping as ORM;

use CASHMusic\Core\CASHDBAL;

class EntityBase
{

    protected $db;
    protected $fillable = [];
    protected $query = null;

    private static $_instance = null;

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
        $object = $db->getRepository(get_called_class())->findOneBy(['id'=>$id]);

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
    public static function findAll($limit=null, $order_by=null, $offset=null)
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
            throw new \Exception("A valid set of values is required to create a CASHMusic\\Model with this static helper method.");
        }
    }

    /**
     * Public shortcut function to save model to database with Doctrine mapping.
     * @return $this
     */
    public function save()
    {

        $this->db->persist($this);
        $this->db->flush();

        return $this;
    }

    /**
     * Public shortcut function to delete a model.
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
        if (!in_array($property, self::$fillable)) return false;

        $custom_method = "set" . ucwords($property) . "Attribute";

        if (method_exists($this, $custom_method)) {
            return $this->$custom_method($value);
        } elseif (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}
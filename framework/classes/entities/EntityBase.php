<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHSystem;
use Doctrine\ORM\Mapping as ORM;
use CASHMusic\Core\CASHDBAL;

/**
 * @ORM\Entity
 */
class EntityBase
{

    protected $db;
    protected $fillable = [];

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
     * @return EntityBase
     */
    public static function find($id)
    {
        return new self();
    }

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
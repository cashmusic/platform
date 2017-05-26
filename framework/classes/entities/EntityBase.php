<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHSystem;
use Doctrine\ORM\Mapping as ORM;
use CASHMusic\Core\CASHDBAL;

/**
 * @ORM\Entity
 */

class EntityBase {

    protected $db;
    protected $fillable = [];


    public function __construct()
    {
        $this->db = CASHDBAL::entityManager();
    }

    public static function find($id) {

    }

    public static function create($values) {

    }

    public function save() {

        $this->db->persist($this);
        $this->db->flush();
    }

    /* by default we just mess with public properties with magic setters/getters. but we can also make our own overrides a la laravel */
    public function __get($property) {
        $custom_method = "get".ucwords($property)."Attribute";

        if(method_exists($this, $custom_method)){
            return $this->$custom_method();
        }
        else if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value) {

        // never let a property be set unless it's in $fillable array
        if (!in_array($property, $this->fillable)) return false;

        $custom_method = "set".ucwords($property)."Attribute";

        if(method_exists($this, $custom_method)){
            return $this->$custom_method($value);
        }
        elseif (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}
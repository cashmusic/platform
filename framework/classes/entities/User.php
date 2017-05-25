<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHSystem;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Entity @Table(name="people")
 **/
class User extends EntityBase {

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    public $username;

    /** @Column(type="string") **/
    public $email_address;

    /** @Column(type="string") **/
    public $first_name;

    /** @Column(type="string") **/
    public $last_name;

    /** @Column(type="string") **/
    public $password;

    public function setPasswordAttribute($value) {
        $this->password = md5($value);
    }
}
<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHDBAL;

/**
 * @Entity(repositoryClass="CASHMusic\Entities\CASHRepository")
 * @Table(name="people")
 */

class People extends EntityBase {

    protected $fillable = ['username', 'email_address', 'last_name', 'password', 'data', 'is_admin'];

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="string") **/
    protected $username;

    /** @Column(type="string") **/
    protected $email_address;

    /** @Column(type="string", nullable=true) **/
    protected $display_name;

    /** @Column(type="string", nullable=true) **/
    protected $first_name;

    /** @Column(type="string", nullable=true) **/
    protected $last_name;

    /** @Column(type="string", nullable=true) **/
    protected $organization;

    /** @Column(type="string") **/
    protected $password;

    /** @Column(type="json_array", nullable=true) **/
    protected $data;

    /** @Column(type="string", nullable=true) **/
    protected $address_line1;

    /** @Column(type="string", nullable=true) **/
    protected $address_line2;

    /** @Column(type="string", nullable=true) **/
    protected $address_city;

    /** @Column(type="string", nullable=true) **/
    protected $address_region;

    /** @Column(type="string", nullable=true) **/
    protected $address_postalcode;

    /** @Column(type="string", nullable=true) **/
    protected $address_country;

    /** @Column(type="string", nullable=true) **/
    protected $url;

    /** @Column(type="boolean") **/
    protected $is_admin;

    /** @Column(type="string", nullable=true) **/
    protected $api_key;

    /** @Column(type="string", nullable=true) **/
    protected $api_secret;

    /** @Column(type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()}) **/
    protected $creation_date;

    /** @Column(type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()}) **/
    protected $modification_date;

    public function setPasswordAttribute($value) {
        $this->password = md5($value);
    }

    /* relationships */
    public function assets($where=false, $limit=false, $order_by=false) {
        return $this->hasMany("Asset", "id", "user_id", $where, $limit, $order_by);
    }

    public function elements($where=false, $limit=false, $order_by=false) {
        return $this->hasMany("Element", "id", "user_id", $where, $limit, $order_by);
    }

    public function lists($conditions=false) {
        return $this->hasMany("PeopleList", "id", "user_id");
    }

    public function mailings($conditions=false) {
        return $this->hasMany("PeopleMailing", "id", "user_id");
    }

    public function resetPassword($conditions=false) {
        return $this->hasMany("PeopleResetPassword", "id", "user_id");
    }

    public function analytics($conditions=false) {
        return $this->hasMany("PeopleAnalytic", "id", "user_id");
    }

    public function basicAnalytics($conditions=false) {
        return $this->hasOne("PeopleAnalyticsBasic", "id", "user_id");
    }

    public function setEmail($email) {
        $this->email_address = $email;
    }
}
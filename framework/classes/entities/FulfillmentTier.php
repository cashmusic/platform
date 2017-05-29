<?php
/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 5/27/17
 * Time: 6:17 PM
 */

namespace CASHMusic\Entities;
/**
 * @Entity @Table(name="commerce_external_fulfillment_tiers")
 */

class FulfillmentTier extends EntityBase
{
    protected $fillable = [];

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer", nullable=true) **/
    protected $creation_date;

    /** @Column(type="integer", nullable=true, options={"default":0}) **/
    protected $modification_date;
}
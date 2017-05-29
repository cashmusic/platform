<?php

namespace CASHMusic\Entities;
/**
 * @Entity @Table(name="assets")
 */


class Asset extends EntityBase
{
    protected $fillable = [];

    /** @Id @Column(type="integer") @GeneratedValue **/
    protected $id;

    /** @Column(type="integer", nullable=true) **/
    protected $user_id;

    /** @Column(type="integer", nullable=true) **/
    protected $parent_id;

    /** @Column(type="string", nullable=true) **/
    protected $location;

    /** @Column(type="string", nullable=true) **/
    protected $public_url;

    /** @Column(type="integer", nullable=true) **/
    protected $connection_id;

    /** @Column(type="string", nullable=true, options={"default":"file"}) **/
    protected $type;

    /** @Column(type="string", nullable=true) **/
    protected $title;

    /** @Column(type="text", nullable=true) **/
    protected $description;

    /** @Column(type="text", nullable=true) **/
    protected $metadata;

    /** @Column(type="boolean", nullable=true, options={"default":0}) **/
    protected $public_status;

    /** @Column(type="integer", nullable=true, options={"default":0}) **/
    protected $size;

    /** @Column(type="string", nullable=true) **/
    protected $hash;

    /** @Column(type="integer", nullable=true) **/
    protected $creation_date;

    /** @Column(type="integer", nullable=true, options={"default":0}) **/
    protected $modification_date;

}
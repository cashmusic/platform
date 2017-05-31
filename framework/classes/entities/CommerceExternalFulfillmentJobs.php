<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentJobs
 *
 * @Table(name="commerce_external_fulfillment_jobs")
 * @Entity
 */
class CommerceExternalFulfillmentJobs extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @Column(name="asset_id", type="integer", nullable=false)
     */
    protected $assetId = '0';

    /**
     * @var string
     *
     * @Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @Column(name="status", type="string", length=255, nullable=false)
     */
    protected $status = 'created';

    /**
     * @var string
     *
     * @Column(name="mappable_fields", type="text", length=16777215, nullable=false)
     */
    protected $mappableFields;

    /**
     * @var integer
     *
     * @Column(name="has_minimum_mappable_fields", type="integer", nullable=false)
     */
    protected $hasMinimumMappableFields = '0';

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modificationDate;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


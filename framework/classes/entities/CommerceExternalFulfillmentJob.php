<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentJobs
 *
 * @Table(name="commerce_external_fulfillment_jobs")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class CommerceExternalFulfillmentJob extends EntityBase
{

    protected $fillable = ['user_id', 'asset_id', 'name', 'description', 'status', 'mappable_fields', 'has_minimum_mappable_fields'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

    /**
     * @var integer
     *
     * @Column(name="asset_id", type="integer", nullable=false)
     */
    protected $asset_id = '0';

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
     * @Column(name="mappable_fields", type="json_array", length=16777215, nullable=false)
     */
    protected $mappable_fields;

    /**
     * @var integer
     *
     * @Column(name="has_minimum_mappable_fields", type="boolean", nullable=false)
     */
    protected $has_minimum_mappable_fields = '0';

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creation_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

    public function tiers($conditions=false) {
        return $this->hasMany("CommerceExternalFulfillmentTier", "id", "fulfillment_job_id", $conditions);
    }

}


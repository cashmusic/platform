<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentJobs
 *
 * @ORM\Table(name="commerce_external_fulfillment_jobs")
 * @ORM\Entity
 */
class CommerceExternalFulfillmentJobs extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="asset_id", type="integer", nullable=false)
     */
    protected $assetId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    protected $status = 'created';

    /**
     * @var string
     *
     * @ORM\Column(name="mappable_fields", type="text", length=16777215, nullable=false)
     */
    protected $mappableFields;

    /**
     * @var integer
     *
     * @ORM\Column(name="has_minimum_mappable_fields", type="integer", nullable=false)
     */
    protected $hasMinimumMappableFields = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

}


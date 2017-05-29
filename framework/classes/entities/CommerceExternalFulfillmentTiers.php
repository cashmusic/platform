<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentTiers
 *
 * @ORM\Table(name="commerce_external_fulfillment_tiers")
 * @ORM\Entity
 */
class CommerceExternalFulfillmentTiers extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="system_job_id", type="integer", nullable=false)
     */
    protected $systemJobId;

    /**
     * @var integer
     *
     * @ORM\Column(name="fulfillment_job_id", type="integer", nullable=false)
     */
    protected $fulfillmentJobId;

    /**
     * @var integer
     *
     * @ORM\Column(name="process_id", type="integer", nullable=false)
     */
    protected $processId;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="upc", type="string", length=255, nullable=true)
     */
    protected $upc;

    /**
     * @var string
     *
     * @ORM\Column(name="metadata", type="text", length=16777215, nullable=true)
     */
    protected $metadata;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    protected $status = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="physical", type="integer", nullable=false)
     */
    protected $physical = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="shipped", type="integer", nullable=false)
     */
    protected $shipped = '0';

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


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceItems
 *
 * @Table(name="commerce_items")
 * @Entity
 */
class CommerceItems extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var string
     *
     * @Column(name="name", type="string", length=255, nullable=true)
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
     * @Column(name="sku", type="string", length=255, nullable=true)
     */
    protected $sku;

    /**
     * @var string
     *
     * @Column(name="price", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $price;

    /**
     * @var string
     *
     * @Column(name="shipping", type="string", length=255, nullable=true)
     */
    protected $shipping;

    /**
     * @var boolean
     *
     * @Column(name="flexible_price", type="boolean", nullable=true)
     */
    protected $flexiblePrice = '0';

    /**
     * @var boolean
     *
     * @Column(name="digital_fulfillment", type="boolean", nullable=true)
     */
    protected $digitalFulfillment = '0';

    /**
     * @var boolean
     *
     * @Column(name="physical_fulfillment", type="boolean", nullable=true)
     */
    protected $physicalFulfillment = '0';

    /**
     * @var integer
     *
     * @Column(name="physical_weight", type="integer", nullable=false)
     */
    protected $physicalWeight;

    /**
     * @var integer
     *
     * @Column(name="physical_width", type="integer", nullable=false)
     */
    protected $physicalWidth;

    /**
     * @var integer
     *
     * @Column(name="physical_height", type="integer", nullable=false)
     */
    protected $physicalHeight;

    /**
     * @var integer
     *
     * @Column(name="physical_depth", type="integer", nullable=false)
     */
    protected $physicalDepth;

    /**
     * @var integer
     *
     * @Column(name="available_units", type="integer", nullable=false)
     */
    protected $availableUnits = '0';

    /**
     * @var boolean
     *
     * @Column(name="variable_pricing", type="boolean", nullable=true)
     */
    protected $variablePricing = '0';

    /**
     * @var integer
     *
     * @Column(name="fulfillment_asset", type="integer", nullable=false)
     */
    protected $fulfillmentAsset = '0';

    /**
     * @var integer
     *
     * @Column(name="descriptive_asset", type="integer", nullable=false)
     */
    protected $descriptiveAsset = '0';

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=false, options={"default": UNIX_TIMESTAMP()})
     */
    protected $creationDate = '0';

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $modificationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


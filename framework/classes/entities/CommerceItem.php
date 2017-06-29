<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceItems
 *
 * @Table(name="commerce_items")
 * @Entity @HasLifecycleCallbacks */
class CommerceItem extends EntityBase
{

    protected $fillable = ['user_id', 'name', 'description', 'sku', 'price', 'shipping', 'flexible_price', 'digital_fulfillment', 'physical_fulfillment', 'physical_weight', 'physical_width', 'physical_height', 'physical_depth', 'available_units', 'variable_pricing', 'fulfillment_asset', 'descriptive_asset'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

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
     * @Column(name="shipping", type="json_array", length=255, nullable=true)
     */
    protected $shipping;

    /**
     * @var boolean
     *
     * @Column(name="flexible_price", type="boolean", nullable=true)
     */
    protected $flexible_price = '0';

    /**
     * @var boolean
     *
     * @Column(name="digital_fulfillment", type="boolean", nullable=true)
     */
    protected $digital_fulfillment = '0';

    /**
     * @var boolean
     *
     * @Column(name="physical_fulfillment", type="boolean", nullable=true)
     */
    protected $physical_fulfillment = '0';

    /**
     * @var integer
     *
     * @Column(name="physical_weight", type="integer", nullable=false)
     */
    protected $physical_weight;

    /**
     * @var integer
     *
     * @Column(name="physical_width", type="integer", nullable=false)
     */
    protected $physical_width;

    /**
     * @var integer
     *
     * @Column(name="physical_height", type="integer", nullable=false)
     */
    protected $physical_height;

    /**
     * @var integer
     *
     * @Column(name="physical_depth", type="integer", nullable=false)
     */
    protected $physical_depth;

    /**
     * @var integer
     *
     * @Column(name="available_units", type="integer", nullable=false)
     */
    protected $available_units = '0';

    /**
     * @var boolean
     *
     * @Column(name="variable_pricing", type="boolean", nullable=true)
     */
    protected $variable_pricing = '0';

    /**
     * @var integer
     *
     * @Column(name="fulfillment_asset", type="integer", nullable=false)
     */
    protected $fulfillment_asset = '0';

    /**
     * @var integer
     *
     * @Column(name="descriptive_asset", type="integer", nullable=false)
     */
    protected $descriptive_asset = '0';

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creation_date = '0';

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


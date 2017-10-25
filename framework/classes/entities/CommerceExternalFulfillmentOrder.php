<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentOrders
 *
 * @Table(name="commerce_external_fulfillment_orders")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class CommerceExternalFulfillmentOrder extends EntityBase
{

    protected $fillable = ['name', 'email', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_province', 'shipping_postal', 'shipping_country', 'complete', 'fulfilled', 'price', 'tier_id', 'order_data', 'notes'];
    /**
     * @var string
     *
     * @Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @Column(name="email", type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @Column(name="shipping_address_1", type="string", length=255, nullable=true)
     */
    protected $shipping_address_1;

    /**
     * @var string
     *
     * @Column(name="shipping_address_2", type="string", length=255, nullable=true)
     */
    protected $shipping_address_2;

    /**
     * @var string
     *
     * @Column(name="shipping_city", type="string", length=255, nullable=true)
     */
    protected $shipping_city;

    /**
     * @var string
     *
     * @Column(name="shipping_province", type="string", length=255, nullable=true)
     */
    protected $shipping_province;

    /**
     * @var string
     *
     * @Column(name="shipping_postal", type="string", length=255, nullable=true)
     */
    protected $shipping_postal;

    /**
     * @var string
     *
     * @Column(name="shipping_country", type="string", length=255, nullable=true)
     */
    protected $shipping_country;

    /**
     * @var integer
     *
     * @Column(name="complete", type="integer", nullable=false)
     */
    protected $complete = '0';

    /**
     * @var integer
     *
     * @Column(name="fulfilled", type="integer", nullable=false)
     */
    protected $fulfilled = '0';

    /**
     * @var string
     *
     * @Column(name="price", type="string", length=255, nullable=true)
     */
    protected $price;

    /**
     * @var integer
     *
     * @Column(name="tier_id", type="integer", nullable=false)
     */
    protected $tier_id;

    /**
     * @var string
     *
     * @Column(name="order_data", type="json_array", length=16777215, nullable=true)
     */
    protected $order_data;

    /**
     * @var string
     *
     * @Column(name="notes", type="text", length=16777215, nullable=true)
     */
    protected $notes;

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

}


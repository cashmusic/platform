<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentOrders
 *
 * @Table(name="commerce_external_fulfillment_orders")
 * @Entity
 */
class CommerceExternalFulfillmentOrders extends EntityBase
{

    protected $fillable;
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
    protected $shippingAddress1;

    /**
     * @var string
     *
     * @Column(name="shipping_address_2", type="string", length=255, nullable=true)
     */
    protected $shippingAddress2;

    /**
     * @var string
     *
     * @Column(name="shipping_city", type="string", length=255, nullable=true)
     */
    protected $shippingCity;

    /**
     * @var string
     *
     * @Column(name="shipping_province", type="string", length=255, nullable=true)
     */
    protected $shippingProvince;

    /**
     * @var string
     *
     * @Column(name="shipping_postal", type="string", length=255, nullable=true)
     */
    protected $shippingPostal;

    /**
     * @var string
     *
     * @Column(name="shipping_country", type="string", length=255, nullable=true)
     */
    protected $shippingCountry;

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
    protected $tierId;

    /**
     * @var string
     *
     * @Column(name="order_data", type="text", length=16777215, nullable=true)
     */
    protected $orderData;

    /**
     * @var string
     *
     * @Column(name="notes", type="text", length=16777215, nullable=true)
     */
    protected $notes;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $modificationDate;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $creationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentOrders
 *
 * @ORM\Table(name="commerce_external_fulfillment_orders")
 * @ORM\Entity
 */
class CommerceExternalFulfillmentOrders extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address_1", type="string", length=255, nullable=true)
     */
    protected $shippingAddress1;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address_2", type="string", length=255, nullable=true)
     */
    protected $shippingAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_city", type="string", length=255, nullable=true)
     */
    protected $shippingCity;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_province", type="string", length=255, nullable=true)
     */
    protected $shippingProvince;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_postal", type="string", length=255, nullable=true)
     */
    protected $shippingPostal;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_country", type="string", length=255, nullable=true)
     */
    protected $shippingCountry;

    /**
     * @var integer
     *
     * @ORM\Column(name="complete", type="integer", nullable=false)
     */
    protected $complete = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="fulfilled", type="integer", nullable=false)
     */
    protected $fulfilled = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", length=255, nullable=true)
     */
    protected $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="tier_id", type="integer", nullable=false)
     */
    protected $tierId;

    /**
     * @var string
     *
     * @ORM\Column(name="order_data", type="text", length=16777215, nullable=true)
     */
    protected $orderData;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=16777215, nullable=true)
     */
    protected $notes;

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


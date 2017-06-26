<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptions
 *
 * @Table(name="commerce_subscriptions")
 * @Entity
 */
class CommerceSubscriptions extends EntityBase
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
     * @var boolean
     *
     * @Column(name="flexible_price", type="boolean", nullable=true)
     */
    protected $flexiblePrice = '0';

    /**
     * @var boolean
     *
     * @Column(name="recurring_payment", type="boolean", nullable=true)
     */
    protected $recurringPayment = '0';

    /**
     * @var integer
     *
     * @Column(name="recurring_interval", type="integer", nullable=false)
     */
    protected $recurringInterval = '0';

    /**
     * @var string
     *
     * @Column(name="interval", type="string", length=255, nullable=false)
     */
    protected $interval = 'month';

    /**
     * @var integer
     *
     * @Column(name="interval_count", type="integer", nullable=false)
     */
    protected $intervalCount = '1';

    /**
     * @var boolean
     *
     * @Column(name="physical", type="boolean", nullable=true)
     */
    protected $physical = '0';

    /**
     * @var string
     *
     * @Column(name="suggested_price", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $suggestedPrice = '0.00';

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creationDate = '0';

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modificationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


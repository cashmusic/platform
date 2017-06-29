<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptions
 *
 * @Table(name="commerce_subscriptions")
 * @Entity @HasLifecycleCallbacks */
class CommerceSubscription extends EntityBase
{

    protected $fillable = ['user_id', 'name', 'description', 'sku', 'price', 'flexible_price', 'recurring_payment', 'recurring_interval', 'interval', 'interval_count', 'physical', 'suggested_price'];
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
     * @var boolean
     *
     * @Column(name="flexible_price", type="boolean", nullable=true)
     */
    protected $flexible_price = '0';

    /**
     * @var boolean
     *
     * @Column(name="recurring_payment", type="boolean", nullable=true)
     */
    protected $recurring_payment = '0';

    /**
     * @var integer
     *
     * @Column(name="recurring_interval", type="integer", nullable=false)
     */
    protected $recurring_interval = '0';

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
    protected $interval_count = '1';

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
    protected $suggested_price = '0.00';

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

    /* relationships */
    public function members($conditions=false) {
        return $this->hasMany("CommerceSubscriptionMember", "id", "subscription_id");
    }

}


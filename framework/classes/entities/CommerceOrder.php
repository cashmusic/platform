<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceOrders
 *
 * @Table(name="commerce_orders")
 * @Entity @HasLifecycleCallbacks */
class CommerceOrder extends EntityBase
{

    protected $fillable = ['user_id', 'customer_user_id', 'transaction_id', 'order_contents', 'fulfilled', 'canceled', 'physical', 'digital', 'notes', 'country_code', 'currency', 'element_id', 'cash_session_id', 'data'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

    /**
     * @var integer
     *
     * @Column(name="customer_user_id", type="integer", nullable=false)
     */
    protected $customer_user_id;

    /**
     * @var integer
     *
     * @Column(name="transaction_id", type="integer", nullable=false)
     */
    protected $transaction_id;

    /**
     * @var string
     *
     * @Column(name="order_contents", type="text", length=65535, nullable=false)
     */
    protected $order_contents;

    /**
     * @var boolean
     *
     * @Column(name="fulfilled", type="boolean", nullable=true)
     */
    protected $fulfilled = '0';

    /**
     * @var boolean
     *
     * @Column(name="canceled", type="boolean", nullable=true)
     */
    protected $canceled = '0';

    /**
     * @var boolean
     *
     * @Column(name="physical", type="boolean", nullable=true)
     */
    protected $physical = '0';

    /**
     * @var boolean
     *
     * @Column(name="digital", type="boolean", nullable=true)
     */
    protected $digital = '0';

    /**
     * @var string
     *
     * @Column(name="notes", type="text", length=65535, nullable=false)
     */
    protected $notes;

    /**
     * @var string
     *
     * @Column(name="country_code", type="string", length=255, nullable=true)
     */
    protected $country_code;

    /**
     * @var string
     *
     * @Column(name="currency", type="string", length=255, nullable=true)
     */
    protected $currency = 'USD';

    /**
     * @var integer
     *
     * @Column(name="element_id", type="integer", nullable=true)
     */
    protected $element_id;

    /**
     * @var string
     *
     * @Column(name="cash_session_id", type="string", length=255, nullable=true)
     */
    protected $cash_session_id;

    /**
     * @var string
     *
     * @Column(name="data", type="json_array", length=65535, nullable=false)
     */
    protected $data;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creation_date;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

    public function customer($conditions=false) {
        return $this->belongsTo("People", "customer_user_id", "id");
    }

    public function transaction($conditions=false) {
        return $this->hasOne("CommerceTransaction", "transaction_id", "id");
    }
}


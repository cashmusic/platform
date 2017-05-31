<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceOrders
 *
 * @Table(name="commerce_orders")
 * @Entity
 */
class CommerceOrders extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @Column(name="customer_user_id", type="integer", nullable=false)
     */
    protected $customerUserId;

    /**
     * @var integer
     *
     * @Column(name="transaction_id", type="integer", nullable=false)
     */
    protected $transactionId;

    /**
     * @var string
     *
     * @Column(name="order_contents", type="text", length=65535, nullable=false)
     */
    protected $orderContents;

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
    protected $countryCode;

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
    protected $elementId;

    /**
     * @var string
     *
     * @Column(name="cash_session_id", type="string", length=255, nullable=true)
     */
    protected $cashSessionId;

    /**
     * @var string
     *
     * @Column(name="data", type="text", length=65535, nullable=false)
     */
    protected $data;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creationDate;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modificationDate = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


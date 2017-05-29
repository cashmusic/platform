<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceOrders
 *
 * @ORM\Table(name="commerce_orders")
 * @ORM\Entity
 */
class CommerceOrders extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_user_id", type="integer", nullable=false)
     */
    protected $customerUserId;

    /**
     * @var integer
     *
     * @ORM\Column(name="transaction_id", type="integer", nullable=false)
     */
    protected $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(name="order_contents", type="text", length=65535, nullable=false)
     */
    protected $orderContents;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fulfilled", type="boolean", nullable=true)
     */
    protected $fulfilled = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="canceled", type="boolean", nullable=true)
     */
    protected $canceled = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="physical", type="boolean", nullable=true)
     */
    protected $physical = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="digital", type="boolean", nullable=true)
     */
    protected $digital = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=65535, nullable=false)
     */
    protected $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=255, nullable=true)
     */
    protected $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=255, nullable=true)
     */
    protected $currency = 'USD';

    /**
     * @var integer
     *
     * @ORM\Column(name="element_id", type="integer", nullable=true)
     */
    protected $elementId;

    /**
     * @var string
     *
     * @ORM\Column(name="cash_session_id", type="string", length=255, nullable=true)
     */
    protected $cashSessionId;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=false)
     */
    protected $data;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modificationDate = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

}


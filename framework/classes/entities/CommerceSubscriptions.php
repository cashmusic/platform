<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptions
 *
 * @ORM\Table(name="commerce_subscriptions")
 * @ORM\Entity
 */
class CommerceSubscriptions extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255, nullable=true)
     */
    protected $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $price;

    /**
     * @var boolean
     *
     * @ORM\Column(name="flexible_price", type="boolean", nullable=true)
     */
    protected $flexiblePrice = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="recurring_payment", type="boolean", nullable=true)
     */
    protected $recurringPayment = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="recurring_interval", type="integer", nullable=false)
     */
    protected $recurringInterval = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="interval", type="string", length=255, nullable=false)
     */
    protected $interval = 'month';

    /**
     * @var integer
     *
     * @ORM\Column(name="interval_count", type="integer", nullable=false)
     */
    protected $intervalCount = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="physical", type="boolean", nullable=true)
     */
    protected $physical = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="suggested_price", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $suggestedPrice = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=false)
     */
    protected $creationDate = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

}


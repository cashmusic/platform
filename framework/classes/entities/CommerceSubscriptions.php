<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptions
 *
 * @ORM\Table(name="commerce_subscriptions")
 * @ORM\Entity
 */
class CommerceSubscriptions
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255, nullable=true)
     */
    private $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=9, scale=2, nullable=true)
     */
    private $price;

    /**
     * @var boolean
     *
     * @ORM\Column(name="flexible_price", type="boolean", nullable=true)
     */
    private $flexiblePrice = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="recurring_payment", type="boolean", nullable=true)
     */
    private $recurringPayment = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="recurring_interval", type="integer", nullable=false)
     */
    private $recurringInterval = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="interval", type="string", length=255, nullable=false)
     */
    private $interval = 'month';

    /**
     * @var integer
     *
     * @ORM\Column(name="interval_count", type="integer", nullable=false)
     */
    private $intervalCount = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="physical", type="boolean", nullable=true)
     */
    private $physical = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="suggested_price", type="decimal", precision=9, scale=2, nullable=true)
     */
    private $suggestedPrice = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=false)
     */
    private $creationDate = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    private $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return CommerceSubscriptions
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CommerceSubscriptions
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return CommerceSubscriptions
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return CommerceSubscriptions
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return CommerceSubscriptions
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set flexiblePrice
     *
     * @param boolean $flexiblePrice
     *
     * @return CommerceSubscriptions
     */
    public function setFlexiblePrice($flexiblePrice)
    {
        $this->flexiblePrice = $flexiblePrice;

        return $this;
    }

    /**
     * Get flexiblePrice
     *
     * @return boolean
     */
    public function getFlexiblePrice()
    {
        return $this->flexiblePrice;
    }

    /**
     * Set recurringPayment
     *
     * @param boolean $recurringPayment
     *
     * @return CommerceSubscriptions
     */
    public function setRecurringPayment($recurringPayment)
    {
        $this->recurringPayment = $recurringPayment;

        return $this;
    }

    /**
     * Get recurringPayment
     *
     * @return boolean
     */
    public function getRecurringPayment()
    {
        return $this->recurringPayment;
    }

    /**
     * Set recurringInterval
     *
     * @param integer $recurringInterval
     *
     * @return CommerceSubscriptions
     */
    public function setRecurringInterval($recurringInterval)
    {
        $this->recurringInterval = $recurringInterval;

        return $this;
    }

    /**
     * Get recurringInterval
     *
     * @return integer
     */
    public function getRecurringInterval()
    {
        return $this->recurringInterval;
    }

    /**
     * Set interval
     *
     * @param string $interval
     *
     * @return CommerceSubscriptions
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Get interval
     *
     * @return string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Set intervalCount
     *
     * @param integer $intervalCount
     *
     * @return CommerceSubscriptions
     */
    public function setIntervalCount($intervalCount)
    {
        $this->intervalCount = $intervalCount;

        return $this;
    }

    /**
     * Get intervalCount
     *
     * @return integer
     */
    public function getIntervalCount()
    {
        return $this->intervalCount;
    }

    /**
     * Set physical
     *
     * @param boolean $physical
     *
     * @return CommerceSubscriptions
     */
    public function setPhysical($physical)
    {
        $this->physical = $physical;

        return $this;
    }

    /**
     * Get physical
     *
     * @return boolean
     */
    public function getPhysical()
    {
        return $this->physical;
    }

    /**
     * Set suggestedPrice
     *
     * @param string $suggestedPrice
     *
     * @return CommerceSubscriptions
     */
    public function setSuggestedPrice($suggestedPrice)
    {
        $this->suggestedPrice = $suggestedPrice;

        return $this;
    }

    /**
     * Get suggestedPrice
     *
     * @return string
     */
    public function getSuggestedPrice()
    {
        return $this->suggestedPrice;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return CommerceSubscriptions
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return integer
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modificationDate
     *
     * @param integer $modificationDate
     *
     * @return CommerceSubscriptions
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return integer
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}


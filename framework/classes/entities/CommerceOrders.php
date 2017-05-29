<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceOrders
 *
 * @ORM\Table(name="commerce_orders")
 * @ORM\Entity
 */
class CommerceOrders
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_user_id", type="integer", nullable=false)
     */
    private $customerUserId;

    /**
     * @var integer
     *
     * @ORM\Column(name="transaction_id", type="integer", nullable=false)
     */
    private $transactionId;

    /**
     * @var string
     *
     * @ORM\Column(name="order_contents", type="text", length=65535, nullable=false)
     */
    private $orderContents;

    /**
     * @var boolean
     *
     * @ORM\Column(name="fulfilled", type="boolean", nullable=true)
     */
    private $fulfilled = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="canceled", type="boolean", nullable=true)
     */
    private $canceled = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="physical", type="boolean", nullable=true)
     */
    private $physical = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="digital", type="boolean", nullable=true)
     */
    private $digital = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=65535, nullable=false)
     */
    private $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=255, nullable=true)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=255, nullable=true)
     */
    private $currency = 'USD';

    /**
     * @var integer
     *
     * @ORM\Column(name="element_id", type="integer", nullable=true)
     */
    private $elementId;

    /**
     * @var string
     *
     * @ORM\Column(name="cash_session_id", type="string", length=255, nullable=true)
     */
    private $cashSessionId;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=false)
     */
    private $data;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    private $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    private $modificationDate = '0';

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
     * @return CommerceOrders
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
     * Set customerUserId
     *
     * @param integer $customerUserId
     *
     * @return CommerceOrders
     */
    public function setCustomerUserId($customerUserId)
    {
        $this->customerUserId = $customerUserId;

        return $this;
    }

    /**
     * Get customerUserId
     *
     * @return integer
     */
    public function getCustomerUserId()
    {
        return $this->customerUserId;
    }

    /**
     * Set transactionId
     *
     * @param integer $transactionId
     *
     * @return CommerceOrders
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Get transactionId
     *
     * @return integer
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set orderContents
     *
     * @param string $orderContents
     *
     * @return CommerceOrders
     */
    public function setOrderContents($orderContents)
    {
        $this->orderContents = $orderContents;

        return $this;
    }

    /**
     * Get orderContents
     *
     * @return string
     */
    public function getOrderContents()
    {
        return $this->orderContents;
    }

    /**
     * Set fulfilled
     *
     * @param boolean $fulfilled
     *
     * @return CommerceOrders
     */
    public function setFulfilled($fulfilled)
    {
        $this->fulfilled = $fulfilled;

        return $this;
    }

    /**
     * Get fulfilled
     *
     * @return boolean
     */
    public function getFulfilled()
    {
        return $this->fulfilled;
    }

    /**
     * Set canceled
     *
     * @param boolean $canceled
     *
     * @return CommerceOrders
     */
    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;

        return $this;
    }

    /**
     * Get canceled
     *
     * @return boolean
     */
    public function getCanceled()
    {
        return $this->canceled;
    }

    /**
     * Set physical
     *
     * @param boolean $physical
     *
     * @return CommerceOrders
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
     * Set digital
     *
     * @param boolean $digital
     *
     * @return CommerceOrders
     */
    public function setDigital($digital)
    {
        $this->digital = $digital;

        return $this;
    }

    /**
     * Get digital
     *
     * @return boolean
     */
    public function getDigital()
    {
        return $this->digital;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return CommerceOrders
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     *
     * @return CommerceOrders
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return CommerceOrders
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set elementId
     *
     * @param integer $elementId
     *
     * @return CommerceOrders
     */
    public function setElementId($elementId)
    {
        $this->elementId = $elementId;

        return $this;
    }

    /**
     * Get elementId
     *
     * @return integer
     */
    public function getElementId()
    {
        return $this->elementId;
    }

    /**
     * Set cashSessionId
     *
     * @param string $cashSessionId
     *
     * @return CommerceOrders
     */
    public function setCashSessionId($cashSessionId)
    {
        $this->cashSessionId = $cashSessionId;

        return $this;
    }

    /**
     * Get cashSessionId
     *
     * @return string
     */
    public function getCashSessionId()
    {
        return $this->cashSessionId;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return CommerceOrders
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return CommerceOrders
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
     * @return CommerceOrders
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


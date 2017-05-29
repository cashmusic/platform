<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceTransactions
 *
 * @ORM\Table(name="commerce_transactions")
 * @ORM\Entity
 */
class CommerceTransactions
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
     * @ORM\Column(name="connection_id", type="integer", nullable=false)
     */
    private $connectionId;

    /**
     * @var string
     *
     * @ORM\Column(name="connection_type", type="string", length=255, nullable=false)
     */
    private $connectionType;

    /**
     * @var string
     *
     * @ORM\Column(name="service_timestamp", type="string", length=255, nullable=false)
     */
    private $serviceTimestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="service_transaction_id", type="string", length=255, nullable=false)
     */
    private $serviceTransactionId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="data_sent", type="text", length=65535, nullable=false)
     */
    private $dataSent;

    /**
     * @var string
     *
     * @ORM\Column(name="data_returned", type="text", length=65535, nullable=false)
     */
    private $dataReturned;

    /**
     * @var boolean
     *
     * @ORM\Column(name="successful", type="boolean", nullable=true)
     */
    private $successful = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="gross_price", type="decimal", precision=9, scale=2, nullable=true)
     */
    private $grossPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="service_fee", type="decimal", precision=9, scale=2, nullable=true)
     */
    private $serviceFee;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=255, nullable=true)
     */
    private $currency = 'USD';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status = 'abandoned';

    /**
     * @var string
     *
     * @ORM\Column(name="parent", type="string", length=255, nullable=false)
     */
    private $parent = 'order';

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    private $parentId = '0';

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
     * @return CommerceTransactions
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
     * Set connectionId
     *
     * @param integer $connectionId
     *
     * @return CommerceTransactions
     */
    public function setConnectionId($connectionId)
    {
        $this->connectionId = $connectionId;

        return $this;
    }

    /**
     * Get connectionId
     *
     * @return integer
     */
    public function getConnectionId()
    {
        return $this->connectionId;
    }

    /**
     * Set connectionType
     *
     * @param string $connectionType
     *
     * @return CommerceTransactions
     */
    public function setConnectionType($connectionType)
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    /**
     * Get connectionType
     *
     * @return string
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    /**
     * Set serviceTimestamp
     *
     * @param string $serviceTimestamp
     *
     * @return CommerceTransactions
     */
    public function setServiceTimestamp($serviceTimestamp)
    {
        $this->serviceTimestamp = $serviceTimestamp;

        return $this;
    }

    /**
     * Get serviceTimestamp
     *
     * @return string
     */
    public function getServiceTimestamp()
    {
        return $this->serviceTimestamp;
    }

    /**
     * Set serviceTransactionId
     *
     * @param string $serviceTransactionId
     *
     * @return CommerceTransactions
     */
    public function setServiceTransactionId($serviceTransactionId)
    {
        $this->serviceTransactionId = $serviceTransactionId;

        return $this;
    }

    /**
     * Get serviceTransactionId
     *
     * @return string
     */
    public function getServiceTransactionId()
    {
        return $this->serviceTransactionId;
    }

    /**
     * Set dataSent
     *
     * @param string $dataSent
     *
     * @return CommerceTransactions
     */
    public function setDataSent($dataSent)
    {
        $this->dataSent = $dataSent;

        return $this;
    }

    /**
     * Get dataSent
     *
     * @return string
     */
    public function getDataSent()
    {
        return $this->dataSent;
    }

    /**
     * Set dataReturned
     *
     * @param string $dataReturned
     *
     * @return CommerceTransactions
     */
    public function setDataReturned($dataReturned)
    {
        $this->dataReturned = $dataReturned;

        return $this;
    }

    /**
     * Get dataReturned
     *
     * @return string
     */
    public function getDataReturned()
    {
        return $this->dataReturned;
    }

    /**
     * Set successful
     *
     * @param boolean $successful
     *
     * @return CommerceTransactions
     */
    public function setSuccessful($successful)
    {
        $this->successful = $successful;

        return $this;
    }

    /**
     * Get successful
     *
     * @return boolean
     */
    public function getSuccessful()
    {
        return $this->successful;
    }

    /**
     * Set grossPrice
     *
     * @param string $grossPrice
     *
     * @return CommerceTransactions
     */
    public function setGrossPrice($grossPrice)
    {
        $this->grossPrice = $grossPrice;

        return $this;
    }

    /**
     * Get grossPrice
     *
     * @return string
     */
    public function getGrossPrice()
    {
        return $this->grossPrice;
    }

    /**
     * Set serviceFee
     *
     * @param string $serviceFee
     *
     * @return CommerceTransactions
     */
    public function setServiceFee($serviceFee)
    {
        $this->serviceFee = $serviceFee;

        return $this;
    }

    /**
     * Get serviceFee
     *
     * @return string
     */
    public function getServiceFee()
    {
        return $this->serviceFee;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return CommerceTransactions
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
     * Set status
     *
     * @param string $status
     *
     * @return CommerceTransactions
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set parent
     *
     * @param string $parent
     *
     * @return CommerceTransactions
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return string
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     *
     * @return CommerceTransactions
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return CommerceTransactions
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
     * @return CommerceTransactions
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


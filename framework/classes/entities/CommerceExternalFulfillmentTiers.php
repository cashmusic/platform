<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentTiers
 *
 * @ORM\Table(name="commerce_external_fulfillment_tiers")
 * @ORM\Entity
 */
class CommerceExternalFulfillmentTiers
{
    /**
     * @var integer
     *
     * @ORM\Column(name="system_job_id", type="integer", nullable=false)
     */
    private $systemJobId;

    /**
     * @var integer
     *
     * @ORM\Column(name="fulfillment_job_id", type="integer", nullable=false)
     */
    private $fulfillmentJobId;

    /**
     * @var integer
     *
     * @ORM\Column(name="process_id", type="integer", nullable=false)
     */
    private $processId;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="upc", type="string", length=255, nullable=true)
     */
    private $upc;

    /**
     * @var string
     *
     * @ORM\Column(name="metadata", type="text", length=16777215, nullable=true)
     */
    private $metadata;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="physical", type="integer", nullable=false)
     */
    private $physical = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="shipped", type="integer", nullable=false)
     */
    private $shipped = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    private $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    private $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


    /**
     * Set systemJobId
     *
     * @param integer $systemJobId
     *
     * @return CommerceExternalFulfillmentTiers
     */
    public function setSystemJobId($systemJobId)
    {
        $this->systemJobId = $systemJobId;

        return $this;
    }

    /**
     * Get systemJobId
     *
     * @return integer
     */
    public function getSystemJobId()
    {
        return $this->systemJobId;
    }

    /**
     * Set fulfillmentJobId
     *
     * @param integer $fulfillmentJobId
     *
     * @return CommerceExternalFulfillmentTiers
     */
    public function setFulfillmentJobId($fulfillmentJobId)
    {
        $this->fulfillmentJobId = $fulfillmentJobId;

        return $this;
    }

    /**
     * Get fulfillmentJobId
     *
     * @return integer
     */
    public function getFulfillmentJobId()
    {
        return $this->fulfillmentJobId;
    }

    /**
     * Set processId
     *
     * @param integer $processId
     *
     * @return CommerceExternalFulfillmentTiers
     */
    public function setProcessId($processId)
    {
        $this->processId = $processId;

        return $this;
    }

    /**
     * Get processId
     *
     * @return integer
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return CommerceExternalFulfillmentTiers
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
     * @return CommerceExternalFulfillmentTiers
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
     * Set upc
     *
     * @param string $upc
     *
     * @return CommerceExternalFulfillmentTiers
     */
    public function setUpc($upc)
    {
        $this->upc = $upc;

        return $this;
    }

    /**
     * Get upc
     *
     * @return string
     */
    public function getUpc()
    {
        return $this->upc;
    }

    /**
     * Set metadata
     *
     * @param string $metadata
     *
     * @return CommerceExternalFulfillmentTiers
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Get metadata
     *
     * @return string
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return CommerceExternalFulfillmentTiers
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set physical
     *
     * @param integer $physical
     *
     * @return CommerceExternalFulfillmentTiers
     */
    public function setPhysical($physical)
    {
        $this->physical = $physical;

        return $this;
    }

    /**
     * Get physical
     *
     * @return integer
     */
    public function getPhysical()
    {
        return $this->physical;
    }

    /**
     * Set shipped
     *
     * @param integer $shipped
     *
     * @return CommerceExternalFulfillmentTiers
     */
    public function setShipped($shipped)
    {
        $this->shipped = $shipped;

        return $this;
    }

    /**
     * Get shipped
     *
     * @return integer
     */
    public function getShipped()
    {
        return $this->shipped;
    }

    /**
     * Set modificationDate
     *
     * @param integer $modificationDate
     *
     * @return CommerceExternalFulfillmentTiers
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
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return CommerceExternalFulfillmentTiers
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}


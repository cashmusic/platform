<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentJobs
 *
 * @ORM\Table(name="commerce_external_fulfillment_jobs")
 * @ORM\Entity
 */
class CommerceExternalFulfillmentJobs
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
     * @ORM\Column(name="asset_id", type="integer", nullable=false)
     */
    private $assetId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
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
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    private $status = 'created';

    /**
     * @var string
     *
     * @ORM\Column(name="mappable_fields", type="text", length=16777215, nullable=false)
     */
    private $mappableFields;

    /**
     * @var integer
     *
     * @ORM\Column(name="has_minimum_mappable_fields", type="integer", nullable=false)
     */
    private $hasMinimumMappableFields = '0';

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
     * Set userId
     *
     * @param integer $userId
     *
     * @return CommerceExternalFulfillmentJobs
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
     * Set assetId
     *
     * @param integer $assetId
     *
     * @return CommerceExternalFulfillmentJobs
     */
    public function setAssetId($assetId)
    {
        $this->assetId = $assetId;

        return $this;
    }

    /**
     * Get assetId
     *
     * @return integer
     */
    public function getAssetId()
    {
        return $this->assetId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return CommerceExternalFulfillmentJobs
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
     * @return CommerceExternalFulfillmentJobs
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
     * Set status
     *
     * @param string $status
     *
     * @return CommerceExternalFulfillmentJobs
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
     * Set mappableFields
     *
     * @param string $mappableFields
     *
     * @return CommerceExternalFulfillmentJobs
     */
    public function setMappableFields($mappableFields)
    {
        $this->mappableFields = $mappableFields;

        return $this;
    }

    /**
     * Get mappableFields
     *
     * @return string
     */
    public function getMappableFields()
    {
        return $this->mappableFields;
    }

    /**
     * Set hasMinimumMappableFields
     *
     * @param integer $hasMinimumMappableFields
     *
     * @return CommerceExternalFulfillmentJobs
     */
    public function setHasMinimumMappableFields($hasMinimumMappableFields)
    {
        $this->hasMinimumMappableFields = $hasMinimumMappableFields;

        return $this;
    }

    /**
     * Get hasMinimumMappableFields
     *
     * @return integer
     */
    public function getHasMinimumMappableFields()
    {
        return $this->hasMinimumMappableFields;
    }

    /**
     * Set modificationDate
     *
     * @param integer $modificationDate
     *
     * @return CommerceExternalFulfillmentJobs
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
     * @return CommerceExternalFulfillmentJobs
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


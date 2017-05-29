<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SystemAnalytics
 *
 * @ORM\Table(name="system_analytics")
 * @ORM\Entity
 */
class SystemAnalytics
{
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="filter", type="string", length=255, nullable=false)
     */
    private $filter;

    /**
     * @var string
     *
     * @ORM\Column(name="primary_value", type="string", length=255, nullable=false)
     */
    private $primaryValue;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", length=65535, nullable=false)
     */
    private $details;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="scope_table_alias", type="text", length=65535, nullable=true)
     */
    private $scopeTableAlias;

    /**
     * @var integer
     *
     * @ORM\Column(name="scope_table_id", type="integer", nullable=true)
     */
    private $scopeTableId;

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
     * Set type
     *
     * @param string $type
     *
     * @return SystemAnalytics
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set filter
     *
     * @param string $filter
     *
     * @return SystemAnalytics
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set primaryValue
     *
     * @param string $primaryValue
     *
     * @return SystemAnalytics
     */
    public function setPrimaryValue($primaryValue)
    {
        $this->primaryValue = $primaryValue;

        return $this;
    }

    /**
     * Get primaryValue
     *
     * @return string
     */
    public function getPrimaryValue()
    {
        return $this->primaryValue;
    }

    /**
     * Set details
     *
     * @param string $details
     *
     * @return SystemAnalytics
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return SystemAnalytics
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
     * Set scopeTableAlias
     *
     * @param string $scopeTableAlias
     *
     * @return SystemAnalytics
     */
    public function setScopeTableAlias($scopeTableAlias)
    {
        $this->scopeTableAlias = $scopeTableAlias;

        return $this;
    }

    /**
     * Get scopeTableAlias
     *
     * @return string
     */
    public function getScopeTableAlias()
    {
        return $this->scopeTableAlias;
    }

    /**
     * Set scopeTableId
     *
     * @param integer $scopeTableId
     *
     * @return SystemAnalytics
     */
    public function setScopeTableId($scopeTableId)
    {
        $this->scopeTableId = $scopeTableId;

        return $this;
    }

    /**
     * Get scopeTableId
     *
     * @return integer
     */
    public function getScopeTableId()
    {
        return $this->scopeTableId;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return SystemAnalytics
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
     * @return SystemAnalytics
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


<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SystemMetadata
 *
 * @ORM\Table(name="system_metadata", indexes={@ORM\Index(name="system_metadata_scope_table", columns={"scope_table_alias", "scope_table_id"})})
 * @ORM\Entity
 */
class SystemMetadata
{
    /**
     * @var string
     *
     * @ORM\Column(name="scope_table_alias", type="string", length=255, nullable=false)
     */
    private $scopeTableAlias = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="scope_table_id", type="integer", nullable=false)
     */
    private $scopeTableId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=false)
     */
    private $value;

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
     * Set scopeTableAlias
     *
     * @param string $scopeTableAlias
     *
     * @return SystemMetadata
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
     * @return SystemMetadata
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
     * Set userId
     *
     * @param integer $userId
     *
     * @return SystemMetadata
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
     * Set type
     *
     * @param string $type
     *
     * @return SystemMetadata
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
     * Set value
     *
     * @param string $value
     *
     * @return SystemMetadata
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return SystemMetadata
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
     * @return SystemMetadata
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


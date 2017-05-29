<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SystemLockCodes
 *
 * @ORM\Table(name="system_lock_codes", indexes={@ORM\Index(name="system_lock_codes_uid", columns={"uid"}), @ORM\Index(name="system_lock_codes_user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class SystemLockCodes
{
    /**
     * @var string
     *
     * @ORM\Column(name="uid", type="string", length=255, nullable=true)
     */
    private $uid;

    /**
     * @var string
     *
     * @ORM\Column(name="scope_table_alias", type="string", length=255, nullable=true)
     */
    private $scopeTableAlias = 'elements';

    /**
     * @var integer
     *
     * @ORM\Column(name="scope_table_id", type="integer", nullable=true)
     */
    private $scopeTableId;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="claim_date", type="integer", nullable=true)
     */
    private $claimDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
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
     * Set uid
     *
     * @param string $uid
     *
     * @return SystemLockCodes
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Set scopeTableAlias
     *
     * @param string $scopeTableAlias
     *
     * @return SystemLockCodes
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
     * @return SystemLockCodes
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
     * @return SystemLockCodes
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
     * Set claimDate
     *
     * @param integer $claimDate
     *
     * @return SystemLockCodes
     */
    public function setClaimDate($claimDate)
    {
        $this->claimDate = $claimDate;

        return $this;
    }

    /**
     * Get claimDate
     *
     * @return integer
     */
    public function getClaimDate()
    {
        return $this->claimDate;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return SystemLockCodes
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
     * @return SystemLockCodes
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


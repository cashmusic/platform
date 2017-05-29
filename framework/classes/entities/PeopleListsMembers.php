<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleListsMembers
 *
 * @ORM\Table(name="people_lists_members", indexes={@ORM\Index(name="people_lists_members_user_id", columns={"user_id"}), @ORM\Index(name="people_lists_members_list_id", columns={"list_id"})})
 * @ORM\Entity
 */
class PeopleListsMembers
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
     * @ORM\Column(name="list_id", type="integer", nullable=false)
     */
    private $listId;

    /**
     * @var string
     *
     * @ORM\Column(name="verification_code", type="text", length=65535, nullable=true)
     */
    private $verificationCode;

    /**
     * @var boolean
     *
     * @ORM\Column(name="verified", type="boolean", nullable=true)
     */
    private $verified = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="initial_comment", type="text", length=65535, nullable=true)
     */
    private $initialComment;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_data", type="text", length=65535, nullable=true)
     */
    private $additionalData;

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
     * @return PeopleListsMembers
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
     * Set listId
     *
     * @param integer $listId
     *
     * @return PeopleListsMembers
     */
    public function setListId($listId)
    {
        $this->listId = $listId;

        return $this;
    }

    /**
     * Get listId
     *
     * @return integer
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * Set verificationCode
     *
     * @param string $verificationCode
     *
     * @return PeopleListsMembers
     */
    public function setVerificationCode($verificationCode)
    {
        $this->verificationCode = $verificationCode;

        return $this;
    }

    /**
     * Get verificationCode
     *
     * @return string
     */
    public function getVerificationCode()
    {
        return $this->verificationCode;
    }

    /**
     * Set verified
     *
     * @param boolean $verified
     *
     * @return PeopleListsMembers
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * Get verified
     *
     * @return boolean
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return PeopleListsMembers
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set initialComment
     *
     * @param string $initialComment
     *
     * @return PeopleListsMembers
     */
    public function setInitialComment($initialComment)
    {
        $this->initialComment = $initialComment;

        return $this;
    }

    /**
     * Get initialComment
     *
     * @return string
     */
    public function getInitialComment()
    {
        return $this->initialComment;
    }

    /**
     * Set additionalData
     *
     * @param string $additionalData
     *
     * @return PeopleListsMembers
     */
    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;

        return $this;
    }

    /**
     * Get additionalData
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return PeopleListsMembers
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
     * @return PeopleListsMembers
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


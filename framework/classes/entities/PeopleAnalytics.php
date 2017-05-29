<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleAnalytics
 *
 * @ORM\Table(name="people_analytics")
 * @ORM\Entity
 */
class PeopleAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="element_id", type="integer", nullable=true)
     */
    private $elementId;

    /**
     * @var integer
     *
     * @ORM\Column(name="access_time", type="integer", nullable=false)
     */
    private $accessTime;

    /**
     * @var string
     *
     * @ORM\Column(name="client_ip", type="string", length=255, nullable=false)
     */
    private $clientIp;

    /**
     * @var string
     *
     * @ORM\Column(name="client_proxy", type="string", length=255, nullable=false)
     */
    private $clientProxy;

    /**
     * @var string
     *
     * @ORM\Column(name="login_method", type="string", length=255, nullable=true)
     */
    private $loginMethod;

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
     * @return PeopleAnalytics
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
     * Set elementId
     *
     * @param integer $elementId
     *
     * @return PeopleAnalytics
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
     * Set accessTime
     *
     * @param integer $accessTime
     *
     * @return PeopleAnalytics
     */
    public function setAccessTime($accessTime)
    {
        $this->accessTime = $accessTime;

        return $this;
    }

    /**
     * Get accessTime
     *
     * @return integer
     */
    public function getAccessTime()
    {
        return $this->accessTime;
    }

    /**
     * Set clientIp
     *
     * @param string $clientIp
     *
     * @return PeopleAnalytics
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }

    /**
     * Get clientIp
     *
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * Set clientProxy
     *
     * @param string $clientProxy
     *
     * @return PeopleAnalytics
     */
    public function setClientProxy($clientProxy)
    {
        $this->clientProxy = $clientProxy;

        return $this;
    }

    /**
     * Get clientProxy
     *
     * @return string
     */
    public function getClientProxy()
    {
        return $this->clientProxy;
    }

    /**
     * Set loginMethod
     *
     * @param string $loginMethod
     *
     * @return PeopleAnalytics
     */
    public function setLoginMethod($loginMethod)
    {
        $this->loginMethod = $loginMethod;

        return $this;
    }

    /**
     * Get loginMethod
     *
     * @return string
     */
    public function getLoginMethod()
    {
        return $this->loginMethod;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return PeopleAnalytics
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
     * @return PeopleAnalytics
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


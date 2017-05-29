<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * ElementsAnalytics
 *
 * @ORM\Table(name="elements_analytics", indexes={@ORM\Index(name="elements_analytics_element_id", columns={"element_id"})})
 * @ORM\Entity
 */
class ElementsAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="element_id", type="integer", nullable=false)
     */
    private $elementId;

    /**
     * @var string
     *
     * @ORM\Column(name="access_method", type="string", length=255, nullable=false)
     */
    private $accessMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="access_location", type="text", length=65535, nullable=false)
     */
    private $accessLocation;

    /**
     * @var string
     *
     * @ORM\Column(name="access_action", type="string", length=255, nullable=false)
     */
    private $accessAction;

    /**
     * @var string
     *
     * @ORM\Column(name="access_data", type="text", length=65535, nullable=false)
     */
    private $accessData;

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
     * @ORM\Column(name="cash_session_id", type="string", length=255, nullable=false)
     */
    private $cashSessionId;

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
     * Set elementId
     *
     * @param integer $elementId
     *
     * @return ElementsAnalytics
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
     * Set accessMethod
     *
     * @param string $accessMethod
     *
     * @return ElementsAnalytics
     */
    public function setAccessMethod($accessMethod)
    {
        $this->accessMethod = $accessMethod;

        return $this;
    }

    /**
     * Get accessMethod
     *
     * @return string
     */
    public function getAccessMethod()
    {
        return $this->accessMethod;
    }

    /**
     * Set accessLocation
     *
     * @param string $accessLocation
     *
     * @return ElementsAnalytics
     */
    public function setAccessLocation($accessLocation)
    {
        $this->accessLocation = $accessLocation;

        return $this;
    }

    /**
     * Get accessLocation
     *
     * @return string
     */
    public function getAccessLocation()
    {
        return $this->accessLocation;
    }

    /**
     * Set accessAction
     *
     * @param string $accessAction
     *
     * @return ElementsAnalytics
     */
    public function setAccessAction($accessAction)
    {
        $this->accessAction = $accessAction;

        return $this;
    }

    /**
     * Get accessAction
     *
     * @return string
     */
    public function getAccessAction()
    {
        return $this->accessAction;
    }

    /**
     * Set accessData
     *
     * @param string $accessData
     *
     * @return ElementsAnalytics
     */
    public function setAccessData($accessData)
    {
        $this->accessData = $accessData;

        return $this;
    }

    /**
     * Get accessData
     *
     * @return string
     */
    public function getAccessData()
    {
        return $this->accessData;
    }

    /**
     * Set accessTime
     *
     * @param integer $accessTime
     *
     * @return ElementsAnalytics
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
     * @return ElementsAnalytics
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
     * @return ElementsAnalytics
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
     * Set cashSessionId
     *
     * @param string $cashSessionId
     *
     * @return ElementsAnalytics
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
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return ElementsAnalytics
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
     * @return ElementsAnalytics
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


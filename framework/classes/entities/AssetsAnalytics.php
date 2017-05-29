<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * AssetsAnalytics
 *
 * @ORM\Table(name="assets_analytics", indexes={@ORM\Index(name="assets_analytics_asset_id", columns={"id"})})
 * @ORM\Entity
 */
class AssetsAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="asset_id", type="integer", nullable=false)
     */
    private $assetId = '0';

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
     * Set assetId
     *
     * @param integer $assetId
     *
     * @return AssetsAnalytics
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
     * Set elementId
     *
     * @param integer $elementId
     *
     * @return AssetsAnalytics
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
     * @return AssetsAnalytics
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
     * @return AssetsAnalytics
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
     * @return AssetsAnalytics
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
     * @return AssetsAnalytics
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
     * @return AssetsAnalytics
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
     * @return AssetsAnalytics
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


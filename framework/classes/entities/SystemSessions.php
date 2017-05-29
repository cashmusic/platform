<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SystemSessions
 *
 * @ORM\Table(name="system_sessions", indexes={@ORM\Index(name="system_sessions_session_id", columns={"session_id"}), @ORM\Index(name="system_sessions_expiration_date", columns={"expiration_date"})})
 * @ORM\Entity
 */
class SystemSessions
{
    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=255, nullable=false)
     */
    private $sessionId;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=false)
     */
    private $data;

    /**
     * @var string
     *
     * @ORM\Column(name="client_ip", type="string", length=255, nullable=true)
     */
    private $clientIp;

    /**
     * @var string
     *
     * @ORM\Column(name="client_proxy", type="string", length=255, nullable=true)
     */
    private $clientProxy;

    /**
     * @var integer
     *
     * @ORM\Column(name="expiration_date", type="integer", nullable=true)
     */
    private $expirationDate;

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
     * Set sessionId
     *
     * @param string $sessionId
     *
     * @return SystemSessions
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    /**
     * Get sessionId
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return SystemSessions
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set clientIp
     *
     * @param string $clientIp
     *
     * @return SystemSessions
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
     * @return SystemSessions
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
     * Set expirationDate
     *
     * @param integer $expirationDate
     *
     * @return SystemSessions
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get expirationDate
     *
     * @return integer
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return SystemSessions
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
     * @return SystemSessions
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


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemSessions
 *
 * @ORM\Table(name="system_sessions", indexes={@ORM\Index(name="system_sessions_session_id", columns={"session_id"}), @ORM\Index(name="system_sessions_expiration_date", columns={"expiration_date"})})
 * @ORM\Entity
 */
class SystemSessions extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @ORM\Column(name="session_id", type="string", length=255, nullable=false)
     */
    protected $sessionId;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=false)
     */
    protected $data;

    /**
     * @var string
     *
     * @ORM\Column(name="client_ip", type="string", length=255, nullable=true)
     */
    protected $clientIp;

    /**
     * @var string
     *
     * @ORM\Column(name="client_proxy", type="string", length=255, nullable=true)
     */
    protected $clientProxy;

    /**
     * @var integer
     *
     * @ORM\Column(name="expiration_date", type="integer", nullable=true)
     */
    protected $expirationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

}


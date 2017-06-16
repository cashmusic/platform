<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemSessions
 *
 * @Table(name="system_sessions", indexes={@Index(name="system_sessions_session_id", columns={"session_id"}), @Index(name="system_sessions_expiration_date", columns={"expiration_date"})})
 * @Entity
 */
class SystemSessions extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @Column(name="session_id", type="string", length=255, nullable=false)
     */
    protected $sessionId;

    /**
     * @var string
     *
     * @Column(name="data", type="text", length=65535, nullable=false)
     */
    protected $data;

    /**
     * @var string
     *
     * @Column(name="client_ip", type="string", length=255, nullable=true)
     */
    protected $clientIp;

    /**
     * @var string
     *
     * @Column(name="client_proxy", type="string", length=255, nullable=true)
     */
    protected $clientProxy;

    /**
     * @var integer
     *
     * @Column(name="expiration_date", type="integer", nullable=true)
     */
    protected $expirationDate;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $creationDate;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $modificationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


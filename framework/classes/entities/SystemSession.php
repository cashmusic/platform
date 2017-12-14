<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemSessions
 *
 * @Table(name="system_sessions", indexes={@Index(name="system_sessions_session_id", columns={"session_id"}), @Index(name="system_sessions_expiration_date", columns={"expiration_date"})})
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class SystemSession extends EntityBase
{

    protected $fillable = ['session_id', 'data', 'client_ip', 'client_proxy', 'expiration_date'];
    /**
     * @var string
     *
     * @Column(name="session_id", type="string", length=255, nullable=false)
     */
    protected $session_id;

    /**
     * @var string
     *
     * @Column(name="data", type="json_array", length=65535, nullable=false)
     */
    protected $data;

    /**
     * @var string
     *
     * @Column(name="client_ip", type="string", length=255, nullable=true)
     */
    protected $client_ip;

    /**
     * @var string
     *
     * @Column(name="client_proxy", type="string", length=255, nullable=true)
     */
    protected $client_proxy;

    /**
     * @var integer
     *
     * @Column(name="expiration_date", type="integer", nullable=true)
     */
    protected $expiration_date;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creation_date;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

/*    public static function findWhere($values, $force_array=false, $limit=null, $order_by=null, $offset=null) {
        $result = parent::findWhere($values, $force_array, $limit, $order_by, $offset);
    }*/

    protected function setDataAttribute($value) {
        // make sure the data value is never actually empty-empty
        if (empty($value)) {
            $value = [];
        }

        $this->data = $value;
    }

}


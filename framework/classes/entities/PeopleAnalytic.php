<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleAnalytic
 *
 * @Table(name="people_analytics")
 * @Entity @HasLifecycleCallbacks */
class PeopleAnalytic extends EntityBase
{

    protected $fillable = ['user_id', 'element_id', 'access_time', 'client_ip', 'client_proxy', 'login_method'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id = '0';

    /**
     * @var integer
     *
     * @Column(name="element_id", type="integer", nullable=true)
     */
    protected $element_id;

    /**
     * @var integer
     *
     * @Column(name="access_time", type="integer", nullable=false)
     */
    protected $access_time;

    /**
     * @var string
     *
     * @Column(name="client_ip", type="string", length=255, nullable=false)
     */
    protected $client_ip;

    /**
     * @var string
     *
     * @Column(name="client_proxy", type="string", length=255, nullable=false)
     */
    protected $client_proxy;

    /**
     * @var string
     *
     * @Column(name="login_method", type="string", length=255, nullable=true)
     */
    protected $login_method;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creation_date;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * ElementsAnalytics
 *
 * @Table(name="elements_analytics", indexes={@Index(name="elements_analytics_element_id", columns={"element_id"})})
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class ElementAnalytic extends EntityBase
{

    protected $fillable = ['element_id', 'access_method', 'access_location', 'access_action', 'access_data', 'access_time', 'client_ip', 'client_proxy', 'cash_session_id'];
    /**
     * @var integer
     *
     * @Column(name="element_id", type="integer", nullable=false)
     */
    protected $element_id;

    /**
     * @var string
     *
     * @Column(name="access_method", type="string", length=255, nullable=false)
     */
    protected $access_method;

    /**
     * @var string
     *
     * @Column(name="access_location", type="text", length=65535, nullable=false)
     */
    protected $access_location;

    /**
     * @var string
     *
     * @Column(name="access_action", type="string", length=255, nullable=false)
     */
    protected $access_action;

    /**
     * @var string
     *
     * @Column(name="access_data", type="json_array", length=65535, nullable=false)
     */
    protected $access_data;

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
     * @Column(name="cash_session_id", type="string", length=255, nullable=false)
     */
    protected $cash_session_id;

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
    protected $modification_date = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


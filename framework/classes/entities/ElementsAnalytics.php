<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * ElementsAnalytics
 *
 * @Table(name="elements_analytics", indexes={@Index(name="elements_analytics_element_id", columns={"element_id"})})
 * @Entity
 */
class ElementsAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="element_id", type="integer", nullable=false)
     */
    protected $elementId;

    /**
     * @var string
     *
     * @Column(name="access_method", type="string", length=255, nullable=false)
     */
    protected $accessMethod;

    /**
     * @var string
     *
     * @Column(name="access_location", type="text", length=65535, nullable=false)
     */
    protected $accessLocation;

    /**
     * @var string
     *
     * @Column(name="access_action", type="string", length=255, nullable=false)
     */
    protected $accessAction;

    /**
     * @var string
     *
     * @Column(name="access_data", type="text", length=65535, nullable=false)
     */
    protected $accessData;

    /**
     * @var integer
     *
     * @Column(name="access_time", type="integer", nullable=false)
     */
    protected $accessTime;

    /**
     * @var string
     *
     * @Column(name="client_ip", type="string", length=255, nullable=false)
     */
    protected $clientIp;

    /**
     * @var string
     *
     * @Column(name="client_proxy", type="string", length=255, nullable=false)
     */
    protected $clientProxy;

    /**
     * @var string
     *
     * @Column(name="cash_session_id", type="string", length=255, nullable=false)
     */
    protected $cashSessionId;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creationDate;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modificationDate = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


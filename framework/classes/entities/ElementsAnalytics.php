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
    protected $elementId;

    /**
     * @var string
     *
     * @ORM\Column(name="access_method", type="string", length=255, nullable=false)
     */
    protected $accessMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="access_location", type="text", length=65535, nullable=false)
     */
    protected $accessLocation;

    /**
     * @var string
     *
     * @ORM\Column(name="access_action", type="string", length=255, nullable=false)
     */
    protected $accessAction;

    /**
     * @var string
     *
     * @ORM\Column(name="access_data", type="text", length=65535, nullable=false)
     */
    protected $accessData;

    /**
     * @var integer
     *
     * @ORM\Column(name="access_time", type="integer", nullable=false)
     */
    protected $accessTime;

    /**
     * @var string
     *
     * @ORM\Column(name="client_ip", type="string", length=255, nullable=false)
     */
    protected $clientIp;

    /**
     * @var string
     *
     * @ORM\Column(name="client_proxy", type="string", length=255, nullable=false)
     */
    protected $clientProxy;

    /**
     * @var string
     *
     * @ORM\Column(name="cash_session_id", type="string", length=255, nullable=false)
     */
    protected $cashSessionId;

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
    protected $modificationDate = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

}


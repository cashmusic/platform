<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleAnalytics
 *
 * @ORM\Table(name="people_analytics")
 * @ORM\Entity
 */
class PeopleAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="element_id", type="integer", nullable=true)
     */
    protected $elementId;

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
     * @ORM\Column(name="login_method", type="string", length=255, nullable=true)
     */
    protected $loginMethod;

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


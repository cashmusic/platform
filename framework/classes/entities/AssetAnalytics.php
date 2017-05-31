<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * AssetAnalytic
 *
 * @Entity @Table(name="assets_analytics", indexes={@ORM\Index(name="assets_analytics_asset_id", columns={"id"})})
 */

class AssetAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="asset_id", type="integer", nullable=false)
     */
    protected $assetId = '0';

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


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * AssetAnalytic
 *
 * @Entity @Table(name="assets_analytics", indexes={@Index(name="assets_analytics_asset_id", columns={"id"})})
 */

class AssetAnalytic extends EntityBase
{

    protected $fillable;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

    /**
     * @var integer
     *
     * @Column(name="asset_id", type="integer", nullable=false)
     */
    protected $assetId = '0';

    /**
     * @var integer
     *
     * @Column(name="element_id", type="integer", nullable=true)
     */
    protected $elementId;

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

}


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

    protected $fillable = ['asset_id', 'element_id', 'access_time', 'client_ip', 'client_proxy', 'cash_session_id'];

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

    /**
     * @var integer
     *
     * @Column(name="asset_id", type="integer", nullable=false)
     */
    protected $asset_id = '0';

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

}


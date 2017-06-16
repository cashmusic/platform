<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * AssetAnalyticsBasic
 *
 * @Table(name="assets_analytics_basic")
 * @Entity
 */
class AssetAnalyticsBasic extends EntityBase
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
     * @Column(name="total", type="integer", nullable=false)
     */
    protected $total;

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
    protected $modificationDate = '0';

}


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * AssetAnalyticsBasic
 *
 * @Table(name="assets_analytics_basic")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
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
    protected $asset_id = '0';

    /**
     * @var integer
     *
     * @Column(name="total", type="integer", nullable=false)
     */
    protected $total;

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


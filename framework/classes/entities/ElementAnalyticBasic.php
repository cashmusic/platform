<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * ElementsAnalyticsBasic
 *
 * @Table(name="elements_analytics_basic")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class ElementAnalyticBasic extends EntityBase
{

    protected $fillable = ['element_id', 'data', 'total'];
    /**
     * @var integer
     *
     * @Column(name="element_id", type="integer", nullable=false)
     */
    protected $element_id;

    /**
     * @var string
     *
     * @Column(name="data", type="json_array", length=65535, nullable=false)
     */
    protected $data;

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

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


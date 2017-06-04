<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * ElementsAnalyticsBasic
 *
 * @Table(name="elements_analytics_basic")
 * @Entity
 */
class ElementAnalyticBasic extends EntityBase
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
     * @Column(name="data", type="text", length=65535, nullable=false)
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


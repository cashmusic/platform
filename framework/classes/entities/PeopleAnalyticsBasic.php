<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleAnalyticsBasic
 *
 * @Table(name="people_analytics_basic")
 * @Entity
 */
class PeopleAnalyticsBasic extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId = '0';

    /**
     * @var integer
     *
     * @Column(name="total", type="integer", nullable=true)
     */
    protected $total = '0';

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


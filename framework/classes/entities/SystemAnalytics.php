<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemAnalytics
 *
 * @Table(name="system_analytics")
 * @Entity @HasLifecycleCallbacks */
class SystemAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @Column(name="type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @Column(name="filter", type="string", length=255, nullable=false)
     */
    protected $filter;

    /**
     * @var string
     *
     * @Column(name="primary_value", type="string", length=255, nullable=false)
     */
    protected $primary_value;

    /**
     * @var string
     *
     * @Column(name="details", type="text", length=65535, nullable=false)
     */
    protected $details;

    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

    /**
     * @var string
     *
     * @Column(name="scope_table_alias", type="text", length=65535, nullable=true)
     */
    protected $scope_table_alias;

    /**
     * @var integer
     *
     * @Column(name="scope_table_id", type="integer", nullable=true)
     */
    protected $scope_table_id;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creation_date;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemAnalytics
 *
 * @Table(name="system_analytics")
 * @Entity
 */
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
    protected $primaryValue;

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
    protected $userId;

    /**
     * @var string
     *
     * @Column(name="scope_table_alias", type="text", length=65535, nullable=true)
     */
    protected $scopeTableAlias;

    /**
     * @var integer
     *
     * @Column(name="scope_table_id", type="integer", nullable=true)
     */
    protected $scopeTableId;

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
    protected $modificationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


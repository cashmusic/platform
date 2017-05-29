<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemAnalytics
 *
 * @ORM\Table(name="system_analytics")
 * @ORM\Entity
 */
class SystemAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="filter", type="string", length=255, nullable=false)
     */
    protected $filter;

    /**
     * @var string
     *
     * @ORM\Column(name="primary_value", type="string", length=255, nullable=false)
     */
    protected $primaryValue;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", length=65535, nullable=false)
     */
    protected $details;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="scope_table_alias", type="text", length=65535, nullable=true)
     */
    protected $scopeTableAlias;

    /**
     * @var integer
     *
     * @ORM\Column(name="scope_table_id", type="integer", nullable=true)
     */
    protected $scopeTableId;

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
    protected $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

}


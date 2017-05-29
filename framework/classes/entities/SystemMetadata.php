<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemMetadata
 *
 * @ORM\Table(name="system_metadata", indexes={@ORM\Index(name="system_metadata_scope_table", columns={"scope_table_alias", "scope_table_id"})})
 * @ORM\Entity
 */
class SystemMetadata extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @ORM\Column(name="scope_table_alias", type="string", length=255, nullable=false)
     */
    protected $scopeTableAlias = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="scope_table_id", type="integer", nullable=false)
     */
    protected $scopeTableId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", length=65535, nullable=false)
     */
    protected $value;

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


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemLockCodes
 *
 * @ORM\Table(name="system_lock_codes", indexes={@ORM\Index(name="system_lock_codes_uid", columns={"uid"}), @ORM\Index(name="system_lock_codes_user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class SystemLockCodes extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @ORM\Column(name="uid", type="string", length=255, nullable=true)
     */
    protected $uid;

    /**
     * @var string
     *
     * @ORM\Column(name="scope_table_alias", type="string", length=255, nullable=true)
     */
    protected $scopeTableAlias = 'elements';

    /**
     * @var integer
     *
     * @ORM\Column(name="scope_table_id", type="integer", nullable=true)
     */
    protected $scopeTableId;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="claim_date", type="integer", nullable=true)
     */
    protected $claimDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creationDate = '0';

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


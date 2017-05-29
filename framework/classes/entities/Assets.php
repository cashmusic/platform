<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * Assets
 *
 * @ORM\Table(name="assets", indexes={@ORM\Index(name="asst_asets_parent_id", columns={"parent_id"}), @ORM\Index(name="assets_user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class Assets extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    protected $parentId;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    protected $location;

    /**
     * @var string
     *
     * @ORM\Column(name="public_url", type="string", length=255, nullable=true)
     */
    protected $publicUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="connection_id", type="integer", nullable=true)
     */
    protected $connectionId;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    protected $type = 'file';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(name="metadata", type="text", length=65535, nullable=true)
     */
    protected $metadata;

    /**
     * @var boolean
     *
     * @ORM\Column(name="public_status", type="boolean", nullable=true)
     */
    protected $publicStatus = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer", nullable=true)
     */
    protected $size = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=true)
     */
    protected $hash;

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
    protected $modificationDate = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
}


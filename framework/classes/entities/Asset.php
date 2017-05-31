<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Asset
 *
 * @Entity @Table(name="assets", indexes={@Index(name="asst_asets_parent_id", columns={"parent_id"}), @Index(name="assets_user_id", columns={"user_id"})})
 */

class Asset extends EntityBase
{

    protected $fillable;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=true)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @Column(name="parent_id", type="integer", nullable=true)
     */
    protected $parentId;

    /**
     * @var string
     *
     * @Column(name="location", type="string", length=255, nullable=true)
     */
    protected $location;

    /**
     * @var string
     *
     * @Column(name="public_url", type="string", length=255, nullable=true)
     */
    protected $publicUrl;

    /**
     * @var integer
     *
     * @Column(name="connection_id", type="integer", nullable=true)
     */
    protected $connectionId;

    /**
     * @var string
     *
     * @Column(name="type", type="string", length=255, nullable=true)
     */
    protected $type = 'file';

    /**
     * @var string
     *
     * @Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @Column(name="description", type="text", length=65535, nullable=true)
     */
    protected $description;

    /**
     * @var string
     *
     * @Column(name="metadata", type="text", length=65535, nullable=true)
     */
    protected $metadata;

    /**
     * @var boolean
     *
     * @Column(name="public_status", type="boolean", nullable=true)
     */
    protected $publicStatus = '0';

    /**
     * @var integer
     *
     * @Column(name="size", type="integer", nullable=true)
     */
    protected $size = '0';

    /**
     * @var string
     *
     * @Column(name="hash", type="string", length=255, nullable=true)
     */
    protected $hash;

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

}


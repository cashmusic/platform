<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemSettings
 *
 * @Table(name="system_settings")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class SystemSettings extends EntityBase
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
     * @Column(name="value", type="json_array", length=65535, nullable=false)
     */
    protected $value;

    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

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
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


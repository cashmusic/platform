<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * Elements
 *
 * @Table(name="elements")
 * @Entity
 */
class Element extends EntityBase
{

    protected $fillable = ['template_id'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=true)
     */
    protected $user_id;

    /**
     * @var integer
     *
     * @Column(name="template_id", type="integer", nullable=true)
     */
    protected $template_id = '-2';

    /**
     * @var string
     *
     * @Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string
     *
     * @Column(name="type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @Column(name="options", type="text", length=65535, nullable=true)
     */
    protected $options;

    /**
     * @var integer
     *
     * @Column(name="license_id", type="integer", nullable=true)
     */
    protected $license_id = '0';

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true)
     */
    protected $creation_date;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

    public function metadata($conditions=false) {
        return $this->hasManyPolymorphic("SystemMetadata", "id", "elements");
    }

}


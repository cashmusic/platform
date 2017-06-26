<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * ElementsCampaigns
 *
 * @Table(name="elements_campaigns")
 * @Entity
 */
class ElementsCampaign extends EntityBase
{

    protected $fillable = ['user_id', 'template_id', 'title', 'description', 'elements', 'metadata'];
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
    protected $template_id = '0';

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
     * @Column(name="elements", type="json_array", length=65535, nullable=true)
     */
    protected $elements;

    /**
     * @var string
     *
     * @Column(name="metadata", type="json_array", length=65535, nullable=true)
     */
    protected $metadata;

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
    protected $modification_date = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


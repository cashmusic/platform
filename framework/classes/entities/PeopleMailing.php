<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleMailings
 *
 * @Table(name="people_mailings")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class PeopleMailing extends EntityBase
{

    protected $fillable = ['user_id','list_id','connection_id','template_id','subject','from_name','html_content','text_content','send_date'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id = '0';

    /**
     * @var integer
     *
     * @Column(name="connection_id", type="integer", nullable=false)
     */
    protected $connection_id = '0';

    /**
     * @var integer
     *
     * @Column(name="list_id", type="integer", nullable=false)
     */
    protected $list_id = '0';

    /**
     * @var integer
     *
     * @Column(name="template_id", type="integer", nullable=true)
     */
    protected $template_id = '0';

    /**
     * @var string
     *
     * @Column(name="subject", type="string", length=255, nullable=true)
     */
    protected $subject;

    /**
     * @var string
     *
     * @Column(name="from_name", type="string", length=255, nullable=true)
     */
    protected $from_name;

    /**
     * @var string
     *
     * @Column(name="html_content", type="text", length=16777215, nullable=true)
     */
    protected $html_content;

    /**
     * @var string
     *
     * @Column(name="text_content", type="text", length=16777215, nullable=true)
     */
    protected $text_content;

    /**
     * @var integer
     *
     * @Column(name="send_date", type="integer", nullable=true)
     */
    protected $send_date;

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


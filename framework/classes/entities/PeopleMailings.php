<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleMailings
 *
 * @Table(name="people_mailings")
 * @Entity
 */
class PeopleMailings extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId = '0';

    /**
     * @var integer
     *
     * @Column(name="connection_id", type="integer", nullable=false)
     */
    protected $connectionId = '0';

    /**
     * @var integer
     *
     * @Column(name="list_id", type="integer", nullable=false)
     */
    protected $listId = '0';

    /**
     * @var integer
     *
     * @Column(name="template_id", type="integer", nullable=true)
     */
    protected $templateId = '0';

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
    protected $fromName;

    /**
     * @var string
     *
     * @Column(name="html_content", type="text", length=16777215, nullable=true)
     */
    protected $htmlContent;

    /**
     * @var string
     *
     * @Column(name="text_content", type="text", length=16777215, nullable=true)
     */
    protected $textContent;

    /**
     * @var integer
     *
     * @Column(name="send_date", type="integer", nullable=true)
     */
    protected $sendDate;

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

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


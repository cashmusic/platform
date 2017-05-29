<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleMailings
 *
 * @ORM\Table(name="people_mailings")
 * @ORM\Entity
 */
class PeopleMailings extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="connection_id", type="integer", nullable=false)
     */
    protected $connectionId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="list_id", type="integer", nullable=false)
     */
    protected $listId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $templateId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="from_name", type="string", length=255, nullable=true)
     */
    protected $fromName;

    /**
     * @var string
     *
     * @ORM\Column(name="html_content", type="text", length=16777215, nullable=true)
     */
    protected $htmlContent;

    /**
     * @var string
     *
     * @ORM\Column(name="text_content", type="text", length=16777215, nullable=true)
     */
    protected $textContent;

    /**
     * @var integer
     *
     * @ORM\Column(name="send_date", type="integer", nullable=true)
     */
    protected $sendDate;

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


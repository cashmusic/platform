<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarEvents
 *
 * @ORM\Table(name="calendar_events", indexes={@ORM\Index(name="calendar_events_user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class CalendarEvents extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="date", type="integer", nullable=true)
     */
    protected $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    protected $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="venue_id", type="string", length=255, nullable=true)
     */
    protected $venueId;

    /**
     * @var boolean
     *
     * @ORM\Column(name="published", type="boolean", nullable=true)
     */
    protected $published;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cancelled", type="boolean", nullable=true)
     */
    protected $cancelled;

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_url", type="string", length=255, nullable=true)
     */
    protected $purchaseUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text", length=65535, nullable=true)
     */
    protected $comments;

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


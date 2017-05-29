<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarGuestlist
 *
 * @ORM\Table(name="calendar_guestlist")
 * @ORM\Entity
 */
class CalendarGuestlist extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     */
    protected $eventId;

    /**
     * @var string
     *
     * @ORM\Column(name="guest_name", type="string", length=255, nullable=true)
     */
    protected $guestName;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_attendees", type="integer", nullable=false)
     */
    protected $totalAttendees = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    protected $comment;

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


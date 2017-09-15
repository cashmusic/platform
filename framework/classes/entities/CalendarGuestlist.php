<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarGuestlist
 *
 * @Table(name="calendar_guestlist")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class CalendarGuestlist extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="event_id", type="integer", nullable=false)
     */
    protected $eventId;

    /**
     * @var string
     *
     * @Column(name="guest_name", type="string", length=255, nullable=true)
     */
    protected $guestName;

    /**
     * @var integer
     *
     * @Column(name="total_attendees", type="integer", nullable=false)
     */
    protected $totalAttendees = '1';

    /**
     * @var string
     *
     * @Column(name="comment", type="text", length=65535, nullable=false)
     */
    protected $comment;

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


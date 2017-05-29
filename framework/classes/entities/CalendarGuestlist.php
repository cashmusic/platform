<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarGuestlist
 *
 * @ORM\Table(name="calendar_guestlist")
 * @ORM\Entity
 */
class CalendarGuestlist
{
    /**
     * @var integer
     *
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     */
    private $eventId;

    /**
     * @var string
     *
     * @ORM\Column(name="guest_name", type="string", length=255, nullable=true)
     */
    private $guestName;

    /**
     * @var integer
     *
     * @ORM\Column(name="total_attendees", type="integer", nullable=false)
     */
    private $totalAttendees = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    private $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    private $modificationDate = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


    /**
     * Set eventId
     *
     * @param integer $eventId
     *
     * @return CalendarGuestlist
     */
    public function setEventId($eventId)
    {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Get eventId
     *
     * @return integer
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * Set guestName
     *
     * @param string $guestName
     *
     * @return CalendarGuestlist
     */
    public function setGuestName($guestName)
    {
        $this->guestName = $guestName;

        return $this;
    }

    /**
     * Get guestName
     *
     * @return string
     */
    public function getGuestName()
    {
        return $this->guestName;
    }

    /**
     * Set totalAttendees
     *
     * @param integer $totalAttendees
     *
     * @return CalendarGuestlist
     */
    public function setTotalAttendees($totalAttendees)
    {
        $this->totalAttendees = $totalAttendees;

        return $this;
    }

    /**
     * Get totalAttendees
     *
     * @return integer
     */
    public function getTotalAttendees()
    {
        return $this->totalAttendees;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return CalendarGuestlist
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return CalendarGuestlist
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return integer
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set modificationDate
     *
     * @param integer $modificationDate
     *
     * @return CalendarGuestlist
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return integer
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}


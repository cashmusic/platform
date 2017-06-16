<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarEvents
 *
 * @Table(name="calendar_events", indexes={@Index(name="calendar_events_user_id", columns={"user_id"})})
 * @Entity
 */
class CalendarEvent extends EntityBase
{

    protected $fillable = ['date', 'user_id', 'venue_id', 'published', 'cancelled', 'purchase_url', 'comments'];
    /**
     * @var integer
     *
     * @Column(name="date", type="integer", nullable=true)
     */
    protected $date;

    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=true)
     */
    protected $user_id;

    /**
     * @var string
     *
     * @Column(name="venue_id", type="string", length=255, nullable=true)
     */
    protected $venue_id;

    /**
     * @var boolean
     *
     * @Column(name="published", type="boolean", nullable=true)
     */
    protected $published;

    /**
     * @var boolean
     *
     * @Column(name="cancelled", type="boolean", nullable=true)
     */
    protected $cancelled;

    /**
     * @var string
     *
     * @Column(name="purchase_url", type="string", length=255, nullable=true)
     */
    protected $purchase_url;

    /**
     * @var string
     *
     * @Column(name="comments", type="text", length=65535, nullable=true)
     */
    protected $comments;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $creation_date;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


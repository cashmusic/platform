<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarEvents
 *
 * @Table(name="calendar_events", indexes={@Index(name="calendar_events_user_id", columns={"user_id"})})
 * @Entity
 */
class CalendarEvents extends EntityBase
{

    protected $fillable;
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
    protected $userId;

    /**
     * @var string
     *
     * @Column(name="venue_id", type="string", length=255, nullable=true)
     */
    protected $venueId;

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
    protected $purchaseUrl;

    /**
     * @var string
     *
     * @Column(name="comments", type="text", length=65535, nullable=true)
     */
    protected $comments;

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
    protected $modificationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


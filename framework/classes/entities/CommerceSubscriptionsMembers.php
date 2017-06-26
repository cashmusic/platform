<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptionsMembers
 *
 * @Table(name="commerce_subscriptions_members", indexes={@Index(name="people_subscr_user_id", columns={"user_id"}), @Index(name="people_subscr_id", columns={"subscription_id"})})
 * @Entity @HasLifecycleCallbacks */
class CommerceSubscriptionsMembers extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=true)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @Column(name="subscription_id", type="integer", nullable=true)
     */
    protected $subscriptionId;

    /**
     * @var string
     *
     * @Column(name="payment_identifier", type="string", length=255, nullable=true)
     */
    protected $paymentIdentifier;

    /**
     * @var string
     *
     * @Column(name="status", type="string", length=255, nullable=true)
     */
    protected $status;

    /**
     * @var integer
     *
     * @Column(name="start_date", type="integer", nullable=true)
     */
    protected $startDate;

    /**
     * @var integer
     *
     * @Column(name="end_date", type="integer", nullable=true)
     */
    protected $endDate;

    /**
     * @var string
     *
     * @Column(name="total_paid_to_date", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $totalPaidToDate;

    /**
     * @var string
     *
     * @Column(name="data", type="text", length=65535, nullable=true)
     */
    protected $data;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creationDate;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modificationDate = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;
}


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptionsMembers
 *
 * @Table(name="commerce_subscriptions_members", indexes={@Index(name="people_subscr_user_id", columns={"user_id"}), @Index(name="people_subscr_id", columns={"subscription_id"})})
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class CommerceSubscriptionMember extends EntityBase
{

    protected $fillable = ['user_id', 'subscription_id', 'payment_identifier', 'status', 'start_date', 'end_date', 'total_paid_to_date', 'data'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=true)
     */
    protected $user_id;

    /**
     * @var integer
     *
     * @Column(name="subscription_id", type="integer", nullable=true)
     */
    protected $subscription_id;

    /**
     * @var string
     *
     * @Column(name="payment_identifier", type="string", length=255, nullable=true)
     */
    protected $payment_identifier;

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
    protected $start_date;

    /**
     * @var integer
     *
     * @Column(name="end_date", type="integer", nullable=true)
     */
    protected $end_date;

    /**
     * @var string
     *
     * @Column(name="total_paid_to_date", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $total_paid_to_date;

    /**
     * @var string
     *
     * @Column(name="data", type="json_array", length=65535, nullable=true)
     */
    protected $data;

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

    public function subscription($conditions=false) {
        return $this->belongsTo("CommerceSubscription", "subscription_id", "id");
    }

    public function customer($conditions=false) {
        return $this->belongsTo("People", "user_id", "id");
    }

    public function getStartDateAttribute() {
        return format_date($this->start_date);
    }
}


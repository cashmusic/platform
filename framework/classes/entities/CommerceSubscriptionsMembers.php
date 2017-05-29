<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptionsMembers
 *
 * @ORM\Table(name="commerce_subscriptions_members", indexes={@ORM\Index(name="people_subscr_user_id", columns={"user_id"}), @ORM\Index(name="people_subscr_id", columns={"subscription_id"})})
 * @ORM\Entity
 */
class CommerceSubscriptionsMembers extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="subscription_id", type="integer", nullable=true)
     */
    protected $subscriptionId;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_identifier", type="string", length=255, nullable=true)
     */
    protected $paymentIdentifier;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    protected $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="start_date", type="integer", nullable=true)
     */
    protected $startDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="end_date", type="integer", nullable=true)
     */
    protected $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="total_paid_to_date", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $totalPaidToDate;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=true)
     */
    protected $data;

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


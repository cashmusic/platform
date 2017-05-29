<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptionsMembers
 *
 * @ORM\Table(name="commerce_subscriptions_members", indexes={@ORM\Index(name="people_subscr_user_id", columns={"user_id"}), @ORM\Index(name="people_subscr_id", columns={"subscription_id"})})
 * @ORM\Entity
 */
class CommerceSubscriptionsMembers
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="subscription_id", type="integer", nullable=true)
     */
    private $subscriptionId;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_identifier", type="string", length=255, nullable=true)
     */
    private $paymentIdentifier;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="start_date", type="integer", nullable=true)
     */
    private $startDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="end_date", type="integer", nullable=true)
     */
    private $endDate;

    /**
     * @var string
     *
     * @ORM\Column(name="total_paid_to_date", type="decimal", precision=9, scale=2, nullable=true)
     */
    private $totalPaidToDate;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=true)
     */
    private $data;

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
     * Set userId
     *
     * @param integer $userId
     *
     * @return CommerceSubscriptionsMembers
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set subscriptionId
     *
     * @param integer $subscriptionId
     *
     * @return CommerceSubscriptionsMembers
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }

    /**
     * Get subscriptionId
     *
     * @return integer
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * Set paymentIdentifier
     *
     * @param string $paymentIdentifier
     *
     * @return CommerceSubscriptionsMembers
     */
    public function setPaymentIdentifier($paymentIdentifier)
    {
        $this->paymentIdentifier = $paymentIdentifier;

        return $this;
    }

    /**
     * Get paymentIdentifier
     *
     * @return string
     */
    public function getPaymentIdentifier()
    {
        return $this->paymentIdentifier;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return CommerceSubscriptionsMembers
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set startDate
     *
     * @param integer $startDate
     *
     * @return CommerceSubscriptionsMembers
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return integer
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param integer $endDate
     *
     * @return CommerceSubscriptionsMembers
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return integer
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set totalPaidToDate
     *
     * @param string $totalPaidToDate
     *
     * @return CommerceSubscriptionsMembers
     */
    public function setTotalPaidToDate($totalPaidToDate)
    {
        $this->totalPaidToDate = $totalPaidToDate;

        return $this;
    }

    /**
     * Get totalPaidToDate
     *
     * @return string
     */
    public function getTotalPaidToDate()
    {
        return $this->totalPaidToDate;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return CommerceSubscriptionsMembers
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return CommerceSubscriptionsMembers
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
     * @return CommerceSubscriptionsMembers
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


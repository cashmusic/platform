<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceExternalFulfillmentOrders
 *
 * @ORM\Table(name="commerce_external_fulfillment_orders")
 * @ORM\Entity
 */
class CommerceExternalFulfillmentOrders
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address_1", type="string", length=255, nullable=true)
     */
    private $shippingAddress1;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address_2", type="string", length=255, nullable=true)
     */
    private $shippingAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_city", type="string", length=255, nullable=true)
     */
    private $shippingCity;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_province", type="string", length=255, nullable=true)
     */
    private $shippingProvince;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_postal", type="string", length=255, nullable=true)
     */
    private $shippingPostal;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_country", type="string", length=255, nullable=true)
     */
    private $shippingCountry;

    /**
     * @var integer
     *
     * @ORM\Column(name="complete", type="integer", nullable=false)
     */
    private $complete = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="fulfilled", type="integer", nullable=false)
     */
    private $fulfilled = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="string", length=255, nullable=true)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="tier_id", type="integer", nullable=false)
     */
    private $tierId;

    /**
     * @var string
     *
     * @ORM\Column(name="order_data", type="text", length=16777215, nullable=true)
     */
    private $orderData;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=16777215, nullable=true)
     */
    private $notes;

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    private $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    private $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


    /**
     * Set name
     *
     * @param string $name
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set shippingAddress1
     *
     * @param string $shippingAddress1
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setShippingAddress1($shippingAddress1)
    {
        $this->shippingAddress1 = $shippingAddress1;

        return $this;
    }

    /**
     * Get shippingAddress1
     *
     * @return string
     */
    public function getShippingAddress1()
    {
        return $this->shippingAddress1;
    }

    /**
     * Set shippingAddress2
     *
     * @param string $shippingAddress2
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setShippingAddress2($shippingAddress2)
    {
        $this->shippingAddress2 = $shippingAddress2;

        return $this;
    }

    /**
     * Get shippingAddress2
     *
     * @return string
     */
    public function getShippingAddress2()
    {
        return $this->shippingAddress2;
    }

    /**
     * Set shippingCity
     *
     * @param string $shippingCity
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setShippingCity($shippingCity)
    {
        $this->shippingCity = $shippingCity;

        return $this;
    }

    /**
     * Get shippingCity
     *
     * @return string
     */
    public function getShippingCity()
    {
        return $this->shippingCity;
    }

    /**
     * Set shippingProvince
     *
     * @param string $shippingProvince
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setShippingProvince($shippingProvince)
    {
        $this->shippingProvince = $shippingProvince;

        return $this;
    }

    /**
     * Get shippingProvince
     *
     * @return string
     */
    public function getShippingProvince()
    {
        return $this->shippingProvince;
    }

    /**
     * Set shippingPostal
     *
     * @param string $shippingPostal
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setShippingPostal($shippingPostal)
    {
        $this->shippingPostal = $shippingPostal;

        return $this;
    }

    /**
     * Get shippingPostal
     *
     * @return string
     */
    public function getShippingPostal()
    {
        return $this->shippingPostal;
    }

    /**
     * Set shippingCountry
     *
     * @param string $shippingCountry
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setShippingCountry($shippingCountry)
    {
        $this->shippingCountry = $shippingCountry;

        return $this;
    }

    /**
     * Get shippingCountry
     *
     * @return string
     */
    public function getShippingCountry()
    {
        return $this->shippingCountry;
    }

    /**
     * Set complete
     *
     * @param integer $complete
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;

        return $this;
    }

    /**
     * Get complete
     *
     * @return integer
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * Set fulfilled
     *
     * @param integer $fulfilled
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setFulfilled($fulfilled)
    {
        $this->fulfilled = $fulfilled;

        return $this;
    }

    /**
     * Get fulfilled
     *
     * @return integer
     */
    public function getFulfilled()
    {
        return $this->fulfilled;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set tierId
     *
     * @param integer $tierId
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setTierId($tierId)
    {
        $this->tierId = $tierId;

        return $this;
    }

    /**
     * Get tierId
     *
     * @return integer
     */
    public function getTierId()
    {
        return $this->tierId;
    }

    /**
     * Set orderData
     *
     * @param string $orderData
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setOrderData($orderData)
    {
        $this->orderData = $orderData;

        return $this;
    }

    /**
     * Get orderData
     *
     * @return string
     */
    public function getOrderData()
    {
        return $this->orderData;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return CommerceExternalFulfillmentOrders
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set modificationDate
     *
     * @param integer $modificationDate
     *
     * @return CommerceExternalFulfillmentOrders
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
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return CommerceExternalFulfillmentOrders
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}


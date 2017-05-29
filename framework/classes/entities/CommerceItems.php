<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceItems
 *
 * @ORM\Table(name="commerce_items")
 * @ORM\Entity
 */
class CommerceItems
{
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="sku", type="string", length=255, nullable=true)
     */
    private $sku;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=9, scale=2, nullable=true)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping", type="string", length=255, nullable=true)
     */
    private $shipping;

    /**
     * @var boolean
     *
     * @ORM\Column(name="flexible_price", type="boolean", nullable=true)
     */
    private $flexiblePrice = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="digital_fulfillment", type="boolean", nullable=true)
     */
    private $digitalFulfillment = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="physical_fulfillment", type="boolean", nullable=true)
     */
    private $physicalFulfillment = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="physical_weight", type="integer", nullable=false)
     */
    private $physicalWeight;

    /**
     * @var integer
     *
     * @ORM\Column(name="physical_width", type="integer", nullable=false)
     */
    private $physicalWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="physical_height", type="integer", nullable=false)
     */
    private $physicalHeight;

    /**
     * @var integer
     *
     * @ORM\Column(name="physical_depth", type="integer", nullable=false)
     */
    private $physicalDepth;

    /**
     * @var integer
     *
     * @ORM\Column(name="available_units", type="integer", nullable=false)
     */
    private $availableUnits = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="variable_pricing", type="boolean", nullable=true)
     */
    private $variablePricing = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="fulfillment_asset", type="integer", nullable=false)
     */
    private $fulfillmentAsset = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="descriptive_asset", type="integer", nullable=false)
     */
    private $descriptiveAsset = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=false)
     */
    private $creationDate = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    private $modificationDate;

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
     * @return CommerceItems
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
     * Set name
     *
     * @param string $name
     *
     * @return CommerceItems
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
     * Set description
     *
     * @param string $description
     *
     * @return CommerceItems
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set sku
     *
     * @param string $sku
     *
     * @return CommerceItems
     */
    public function setSku($sku)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Get sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return CommerceItems
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
     * Set shipping
     *
     * @param string $shipping
     *
     * @return CommerceItems
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * Get shipping
     *
     * @return string
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Set flexiblePrice
     *
     * @param boolean $flexiblePrice
     *
     * @return CommerceItems
     */
    public function setFlexiblePrice($flexiblePrice)
    {
        $this->flexiblePrice = $flexiblePrice;

        return $this;
    }

    /**
     * Get flexiblePrice
     *
     * @return boolean
     */
    public function getFlexiblePrice()
    {
        return $this->flexiblePrice;
    }

    /**
     * Set digitalFulfillment
     *
     * @param boolean $digitalFulfillment
     *
     * @return CommerceItems
     */
    public function setDigitalFulfillment($digitalFulfillment)
    {
        $this->digitalFulfillment = $digitalFulfillment;

        return $this;
    }

    /**
     * Get digitalFulfillment
     *
     * @return boolean
     */
    public function getDigitalFulfillment()
    {
        return $this->digitalFulfillment;
    }

    /**
     * Set physicalFulfillment
     *
     * @param boolean $physicalFulfillment
     *
     * @return CommerceItems
     */
    public function setPhysicalFulfillment($physicalFulfillment)
    {
        $this->physicalFulfillment = $physicalFulfillment;

        return $this;
    }

    /**
     * Get physicalFulfillment
     *
     * @return boolean
     */
    public function getPhysicalFulfillment()
    {
        return $this->physicalFulfillment;
    }

    /**
     * Set physicalWeight
     *
     * @param integer $physicalWeight
     *
     * @return CommerceItems
     */
    public function setPhysicalWeight($physicalWeight)
    {
        $this->physicalWeight = $physicalWeight;

        return $this;
    }

    /**
     * Get physicalWeight
     *
     * @return integer
     */
    public function getPhysicalWeight()
    {
        return $this->physicalWeight;
    }

    /**
     * Set physicalWidth
     *
     * @param integer $physicalWidth
     *
     * @return CommerceItems
     */
    public function setPhysicalWidth($physicalWidth)
    {
        $this->physicalWidth = $physicalWidth;

        return $this;
    }

    /**
     * Get physicalWidth
     *
     * @return integer
     */
    public function getPhysicalWidth()
    {
        return $this->physicalWidth;
    }

    /**
     * Set physicalHeight
     *
     * @param integer $physicalHeight
     *
     * @return CommerceItems
     */
    public function setPhysicalHeight($physicalHeight)
    {
        $this->physicalHeight = $physicalHeight;

        return $this;
    }

    /**
     * Get physicalHeight
     *
     * @return integer
     */
    public function getPhysicalHeight()
    {
        return $this->physicalHeight;
    }

    /**
     * Set physicalDepth
     *
     * @param integer $physicalDepth
     *
     * @return CommerceItems
     */
    public function setPhysicalDepth($physicalDepth)
    {
        $this->physicalDepth = $physicalDepth;

        return $this;
    }

    /**
     * Get physicalDepth
     *
     * @return integer
     */
    public function getPhysicalDepth()
    {
        return $this->physicalDepth;
    }

    /**
     * Set availableUnits
     *
     * @param integer $availableUnits
     *
     * @return CommerceItems
     */
    public function setAvailableUnits($availableUnits)
    {
        $this->availableUnits = $availableUnits;

        return $this;
    }

    /**
     * Get availableUnits
     *
     * @return integer
     */
    public function getAvailableUnits()
    {
        return $this->availableUnits;
    }

    /**
     * Set variablePricing
     *
     * @param boolean $variablePricing
     *
     * @return CommerceItems
     */
    public function setVariablePricing($variablePricing)
    {
        $this->variablePricing = $variablePricing;

        return $this;
    }

    /**
     * Get variablePricing
     *
     * @return boolean
     */
    public function getVariablePricing()
    {
        return $this->variablePricing;
    }

    /**
     * Set fulfillmentAsset
     *
     * @param integer $fulfillmentAsset
     *
     * @return CommerceItems
     */
    public function setFulfillmentAsset($fulfillmentAsset)
    {
        $this->fulfillmentAsset = $fulfillmentAsset;

        return $this;
    }

    /**
     * Get fulfillmentAsset
     *
     * @return integer
     */
    public function getFulfillmentAsset()
    {
        return $this->fulfillmentAsset;
    }

    /**
     * Set descriptiveAsset
     *
     * @param integer $descriptiveAsset
     *
     * @return CommerceItems
     */
    public function setDescriptiveAsset($descriptiveAsset)
    {
        $this->descriptiveAsset = $descriptiveAsset;

        return $this;
    }

    /**
     * Get descriptiveAsset
     *
     * @return integer
     */
    public function getDescriptiveAsset()
    {
        return $this->descriptiveAsset;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return CommerceItems
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
     * @return CommerceItems
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


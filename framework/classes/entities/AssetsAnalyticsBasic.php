<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * AssetsAnalyticsBasic
 *
 * @ORM\Table(name="assets_analytics_basic")
 * @ORM\Entity
 */
class AssetsAnalyticsBasic
{
    /**
     * @var integer
     *
     * @ORM\Column(name="asset_id", type="integer", nullable=false)
     */
    private $assetId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="total", type="integer", nullable=false)
     */
    private $total;

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
     * Set assetId
     *
     * @param integer $assetId
     *
     * @return AssetsAnalyticsBasic
     */
    public function setAssetId($assetId)
    {
        $this->assetId = $assetId;

        return $this;
    }

    /**
     * Get assetId
     *
     * @return integer
     */
    public function getAssetId()
    {
        return $this->assetId;
    }

    /**
     * Set total
     *
     * @param integer $total
     *
     * @return AssetsAnalyticsBasic
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return AssetsAnalyticsBasic
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
     * @return AssetsAnalyticsBasic
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


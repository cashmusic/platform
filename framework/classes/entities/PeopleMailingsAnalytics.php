<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleMailingsAnalytics
 *
 * @ORM\Table(name="people_mailings_analytics")
 * @ORM\Entity
 */
class PeopleMailingsAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="mailing_id", type="integer", nullable=false)
     */
    private $mailingId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="sends", type="integer", nullable=true)
     */
    private $sends = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="opens_total", type="integer", nullable=true)
     */
    private $opensTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="opens_unique", type="integer", nullable=true)
     */
    private $opensUnique = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="opens_mobile", type="integer", nullable=true)
     */
    private $opensMobile = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="opens_country", type="text", length=16777215, nullable=true)
     */
    private $opensCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="opens_ids", type="text", length=16777215, nullable=true)
     */
    private $opensIds;

    /**
     * @var integer
     *
     * @ORM\Column(name="clicks", type="integer", nullable=true)
     */
    private $clicks = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="clicks_urls", type="text", length=65535, nullable=true)
     */
    private $clicksUrls;

    /**
     * @var integer
     *
     * @ORM\Column(name="failures", type="integer", nullable=true)
     */
    private $failures = '0';

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
     * Set mailingId
     *
     * @param integer $mailingId
     *
     * @return PeopleMailingsAnalytics
     */
    public function setMailingId($mailingId)
    {
        $this->mailingId = $mailingId;

        return $this;
    }

    /**
     * Get mailingId
     *
     * @return integer
     */
    public function getMailingId()
    {
        return $this->mailingId;
    }

    /**
     * Set sends
     *
     * @param integer $sends
     *
     * @return PeopleMailingsAnalytics
     */
    public function setSends($sends)
    {
        $this->sends = $sends;

        return $this;
    }

    /**
     * Get sends
     *
     * @return integer
     */
    public function getSends()
    {
        return $this->sends;
    }

    /**
     * Set opensTotal
     *
     * @param integer $opensTotal
     *
     * @return PeopleMailingsAnalytics
     */
    public function setOpensTotal($opensTotal)
    {
        $this->opensTotal = $opensTotal;

        return $this;
    }

    /**
     * Get opensTotal
     *
     * @return integer
     */
    public function getOpensTotal()
    {
        return $this->opensTotal;
    }

    /**
     * Set opensUnique
     *
     * @param integer $opensUnique
     *
     * @return PeopleMailingsAnalytics
     */
    public function setOpensUnique($opensUnique)
    {
        $this->opensUnique = $opensUnique;

        return $this;
    }

    /**
     * Get opensUnique
     *
     * @return integer
     */
    public function getOpensUnique()
    {
        return $this->opensUnique;
    }

    /**
     * Set opensMobile
     *
     * @param integer $opensMobile
     *
     * @return PeopleMailingsAnalytics
     */
    public function setOpensMobile($opensMobile)
    {
        $this->opensMobile = $opensMobile;

        return $this;
    }

    /**
     * Get opensMobile
     *
     * @return integer
     */
    public function getOpensMobile()
    {
        return $this->opensMobile;
    }

    /**
     * Set opensCountry
     *
     * @param string $opensCountry
     *
     * @return PeopleMailingsAnalytics
     */
    public function setOpensCountry($opensCountry)
    {
        $this->opensCountry = $opensCountry;

        return $this;
    }

    /**
     * Get opensCountry
     *
     * @return string
     */
    public function getOpensCountry()
    {
        return $this->opensCountry;
    }

    /**
     * Set opensIds
     *
     * @param string $opensIds
     *
     * @return PeopleMailingsAnalytics
     */
    public function setOpensIds($opensIds)
    {
        $this->opensIds = $opensIds;

        return $this;
    }

    /**
     * Get opensIds
     *
     * @return string
     */
    public function getOpensIds()
    {
        return $this->opensIds;
    }

    /**
     * Set clicks
     *
     * @param integer $clicks
     *
     * @return PeopleMailingsAnalytics
     */
    public function setClicks($clicks)
    {
        $this->clicks = $clicks;

        return $this;
    }

    /**
     * Get clicks
     *
     * @return integer
     */
    public function getClicks()
    {
        return $this->clicks;
    }

    /**
     * Set clicksUrls
     *
     * @param string $clicksUrls
     *
     * @return PeopleMailingsAnalytics
     */
    public function setClicksUrls($clicksUrls)
    {
        $this->clicksUrls = $clicksUrls;

        return $this;
    }

    /**
     * Get clicksUrls
     *
     * @return string
     */
    public function getClicksUrls()
    {
        return $this->clicksUrls;
    }

    /**
     * Set failures
     *
     * @param integer $failures
     *
     * @return PeopleMailingsAnalytics
     */
    public function setFailures($failures)
    {
        $this->failures = $failures;

        return $this;
    }

    /**
     * Get failures
     *
     * @return integer
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return PeopleMailingsAnalytics
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
     * @return PeopleMailingsAnalytics
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


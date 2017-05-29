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
    protected $mailingId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="sends", type="integer", nullable=true)
     */
    protected $sends = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="opens_total", type="integer", nullable=true)
     */
    protected $opensTotal = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="opens_unique", type="integer", nullable=true)
     */
    protected $opensUnique = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="opens_mobile", type="integer", nullable=true)
     */
    protected $opensMobile = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="opens_country", type="text", length=16777215, nullable=true)
     */
    protected $opensCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="opens_ids", type="text", length=16777215, nullable=true)
     */
    protected $opensIds;

    /**
     * @var integer
     *
     * @ORM\Column(name="clicks", type="integer", nullable=true)
     */
    protected $clicks = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="clicks_urls", type="text", length=65535, nullable=true)
     */
    protected $clicksUrls;

    /**
     * @var integer
     *
     * @ORM\Column(name="failures", type="integer", nullable=true)
     */
    protected $failures = '0';

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


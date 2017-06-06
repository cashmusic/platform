<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleMailingsAnalytics
 *
 * @Table(name="people_mailings_analytics")
 * @Entity
 */
class PeopleMailingsAnalytics extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="mailing_id", type="integer", nullable=false)
     */
    protected $mailingId = '0';

    /**
     * @var integer
     *
     * @Column(name="sends", type="integer", nullable=true)
     */
    protected $sends = '0';

    /**
     * @var integer
     *
     * @Column(name="opens_total", type="integer", nullable=true)
     */
    protected $opensTotal = '0';

    /**
     * @var integer
     *
     * @Column(name="opens_unique", type="integer", nullable=true)
     */
    protected $opensUnique = '0';

    /**
     * @var integer
     *
     * @Column(name="opens_mobile", type="integer", nullable=true)
     */
    protected $opensMobile = '0';

    /**
     * @var string
     *
     * @Column(name="opens_country", type="text", length=16777215, nullable=true)
     */
    protected $opensCountry;

    /**
     * @var string
     *
     * @Column(name="opens_ids", type="text", length=16777215, nullable=true)
     */
    protected $opensIds;

    /**
     * @var integer
     *
     * @Column(name="clicks", type="integer", nullable=true)
     */
    protected $clicks = '0';

    /**
     * @var string
     *
     * @Column(name="clicks_urls", type="text", length=65535, nullable=true)
     */
    protected $clicksUrls;

    /**
     * @var integer
     *
     * @Column(name="failures", type="integer", nullable=true)
     */
    protected $failures = '0';

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
    protected $modificationDate = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


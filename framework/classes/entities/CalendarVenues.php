<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarVenues
 *
 * @ORM\Table(name="calendar_venues")
 * @ORM\Entity
 */
class CalendarVenues extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", length=65535, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address1", type="text", length=65535, nullable=true)
     */
    protected $address1;

    /**
     * @var string
     *
     * @ORM\Column(name="address2", type="text", length=65535, nullable=true)
     */
    protected $address2;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="text", length=65535, nullable=true)
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="text", length=65535, nullable=true)
     */
    protected $region;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="text", length=65535, nullable=true)
     */
    protected $country;

    /**
     * @var string
     *
     * @ORM\Column(name="postalcode", type="text", length=65535, nullable=true)
     */
    protected $postalcode;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", precision=10, scale=0, nullable=true)
     */
    protected $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", precision=10, scale=0, nullable=true)
     */
    protected $longitude;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text", length=65535, nullable=true)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="text", length=65535, nullable=true)
     */
    protected $phone;

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
    protected $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=true)
     */
    protected $userId = '-1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

}


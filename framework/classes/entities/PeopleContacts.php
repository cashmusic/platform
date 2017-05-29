<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleContacts
 *
 * @ORM\Table(name="people_contacts")
 * @ORM\Entity
 */
class PeopleContacts extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=255, nullable=false)
     */
    protected $emailAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="organization", type="string", length=255, nullable=true)
     */
    protected $organization;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line1", type="string", length=255, nullable=true)
     */
    protected $addressLine1;

    /**
     * @var string
     *
     * @ORM\Column(name="address_line2", type="string", length=255, nullable=true)
     */
    protected $addressLine2;

    /**
     * @var string
     *
     * @ORM\Column(name="address_city", type="string", length=255, nullable=true)
     */
    protected $addressCity;

    /**
     * @var string
     *
     * @ORM\Column(name="address_region", type="string", length=255, nullable=true)
     */
    protected $addressRegion;

    /**
     * @var string
     *
     * @ORM\Column(name="address_postalcode", type="string", length=255, nullable=true)
     */
    protected $addressPostalcode;

    /**
     * @var string
     *
     * @ORM\Column(name="address_country", type="string", length=255, nullable=true)
     */
    protected $addressCountry;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", length=65535, nullable=true)
     */
    protected $notes;

    /**
     * @var string
     *
     * @ORM\Column(name="links", type="text", length=65535, nullable=true)
     */
    protected $links;

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
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

}


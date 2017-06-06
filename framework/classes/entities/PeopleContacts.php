<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleContacts
 *
 * @Table(name="people_contacts")
 * @Entity
 */
class PeopleContacts extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var string
     *
     * @Column(name="email_address", type="string", length=255, nullable=false)
     */
    protected $emailAddress;

    /**
     * @var string
     *
     * @Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @Column(name="organization", type="string", length=255, nullable=true)
     */
    protected $organization;

    /**
     * @var string
     *
     * @Column(name="address_line1", type="string", length=255, nullable=true)
     */
    protected $addressLine1;

    /**
     * @var string
     *
     * @Column(name="address_line2", type="string", length=255, nullable=true)
     */
    protected $addressLine2;

    /**
     * @var string
     *
     * @Column(name="address_city", type="string", length=255, nullable=true)
     */
    protected $addressCity;

    /**
     * @var string
     *
     * @Column(name="address_region", type="string", length=255, nullable=true)
     */
    protected $addressRegion;

    /**
     * @var string
     *
     * @Column(name="address_postalcode", type="string", length=255, nullable=true)
     */
    protected $addressPostalcode;

    /**
     * @var string
     *
     * @Column(name="address_country", type="string", length=255, nullable=true)
     */
    protected $addressCountry;

    /**
     * @var string
     *
     * @Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @Column(name="notes", type="text", length=65535, nullable=true)
     */
    protected $notes;

    /**
     * @var string
     *
     * @Column(name="links", type="text", length=65535, nullable=true)
     */
    protected $links;

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
    protected $modificationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


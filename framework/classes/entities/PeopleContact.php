<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleContacts
 *
 * @Table(name="people_contacts")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class PeopleContact extends EntityBase
{

    protected $fillable = ['email_address', 'user_id', 'first_name', 'last_name', 'organization', 'address_line1', 'address_line2', 'address_city', 'address_region', 'address_postalcode', 'address_country', 'phone', 'notes', 'links'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

    /**
     * @var string
     *
     * @Column(name="email_address", type="string", length=255, nullable=false)
     */
    protected $email_address;

    /**
     * @var string
     *
     * @Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $first_name;

    /**
     * @var string
     *
     * @Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $last_name;

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
    protected $address_line1;

    /**
     * @var string
     *
     * @Column(name="address_line2", type="string", length=255, nullable=true)
     */
    protected $address_line2;

    /**
     * @var string
     *
     * @Column(name="address_city", type="string", length=255, nullable=true)
     */
    protected $address_city;

    /**
     * @var string
     *
     * @Column(name="address_region", type="string", length=255, nullable=true)
     */
    protected $address_region;

    /**
     * @var string
     *
     * @Column(name="address_postalcode", type="string", length=255, nullable=true)
     */
    protected $address_postalcode;

    /**
     * @var string
     *
     * @Column(name="address_country", type="string", length=255, nullable=true)
     */
    protected $address_country;

    /**
     * @var string
     *
     * @Column(name="phone", type="string", length=255, nullable=true)
     */
    protected $phone;

    /**
     * @var string
     *
     * @Column(name="notes", type="json_array", length=65535, nullable=true)
     */
    protected $notes;

    /**
     * @var string
     *
     * @Column(name="links", type="json_array", length=65535, nullable=true)
     */
    protected $links;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creation_date;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


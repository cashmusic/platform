<?php

namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * People
 *
 * @ORM\Table(name="people", indexes={@ORM\Index(name="email", columns={"email_address"})})
 * @ORM\Entity
 */
class People extends EntityBase
{

    protected $fillable = ['username', 'email_address', 'last_name', 'password', 'data'];

    /**
     * @var string
     *
     * @ORM\Column(name="email_address", type="string", length=255, nullable=false)
     */
    protected $emailAddress = '';

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    protected $password = '';

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     */
    protected $username = '';

    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255, nullable=true)
     */
    protected $displayName;

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
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_admin", type="boolean", nullable=false)
     */
    protected $isAdmin = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=65535, nullable=true)
     */
    protected $data;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", length=64, nullable=true)
     */
    protected $apiKey = '';

    /**
     * @var string
     *
     * @ORM\Column(name="api_secret", type="string", length=64, nullable=true)
     */
    protected $apiSecret = '';

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

    protected function setPasswordAttribute($value)
    {
        $this->password = md5($value);
    }
}


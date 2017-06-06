<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleResetPassword
 *
 * @Table(name="people_resetpassword")
 * @Entity
 */
class PeopleResetPassword extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @Column(name="key", type="string", length=255, nullable=false)
     */
    protected $key;

    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId = '0';

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


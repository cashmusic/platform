<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleListsMembers
 *
 * @Table(name="people_lists_members", indexes={@Index(name="people_lists_members_user_id", columns={"user_id"}), @Index(name="people_lists_members_list_id", columns={"list_id"})})
 * @Entity
 */
class PeopleListsMembers extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @Column(name="list_id", type="integer", nullable=false)
     */
    protected $listId;

    /**
     * @var string
     *
     * @Column(name="verification_code", type="text", length=65535, nullable=true)
     */
    protected $verificationCode;

    /**
     * @var boolean
     *
     * @Column(name="verified", type="boolean", nullable=true)
     */
    protected $verified = '0';

    /**
     * @var boolean
     *
     * @Column(name="active", type="boolean", nullable=true)
     */
    protected $active = '1';

    /**
     * @var string
     *
     * @Column(name="initial_comment", type="text", length=65535, nullable=true)
     */
    protected $initialComment;

    /**
     * @var string
     *
     * @Column(name="additional_data", type="text", length=65535, nullable=true)
     */
    protected $additionalData;

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


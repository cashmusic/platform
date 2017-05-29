<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * PeopleListsMembers
 *
 * @ORM\Table(name="people_lists_members", indexes={@ORM\Index(name="people_lists_members_user_id", columns={"user_id"}), @ORM\Index(name="people_lists_members_list_id", columns={"list_id"})})
 * @ORM\Entity
 */
class PeopleListsMembers extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="list_id", type="integer", nullable=false)
     */
    protected $listId;

    /**
     * @var string
     *
     * @ORM\Column(name="verification_code", type="text", length=65535, nullable=true)
     */
    protected $verificationCode;

    /**
     * @var boolean
     *
     * @ORM\Column(name="verified", type="boolean", nullable=true)
     */
    protected $verified = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    protected $active = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="initial_comment", type="text", length=65535, nullable=true)
     */
    protected $initialComment;

    /**
     * @var string
     *
     * @ORM\Column(name="additional_data", type="text", length=65535, nullable=true)
     */
    protected $additionalData;

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


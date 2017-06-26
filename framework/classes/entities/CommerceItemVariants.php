<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceItemVariants
 *
 * @Table(name="commerce_item_variants")
 * @Entity
 */
class CommerceItemVariants extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="item_id", type="integer", nullable=false)
     */
    protected $itemId;

    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $userId;

    /**
     * @var string
     *
     * @Column(name="attributes", type="string", length=255, nullable=false)
     */
    protected $attributes;

    /**
     * @var integer
     *
     * @Column(name="quantity", type="integer", nullable=false)
     */
    protected $quantity = '0';

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creationDate = '0';

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modificationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceItemVariants
 *
 * @Table(name="commerce_item_variants")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class CommerceItemVariant extends EntityBase
{

    protected $fillable = ['item_id', 'user_id', 'attributes', 'quantity'];
    /**
     * @var integer
     *
     * @Column(name="item_id", type="integer", nullable=false)
     */
    protected $item_id;

    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

    /**
     * @var string
     *
     * @Column(name="attributes", type="json_array", length=255, nullable=false)
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
    protected $creation_date = '0';

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


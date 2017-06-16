<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptionsIncludedItems
 *
 * @Table(name="commerce_subscriptions_included_items")
 * @Entity
 */
class CommerceSubscriptionsIncludedItems extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="subscription_id", type="integer", nullable=false)
     */
    protected $subscriptionId;

    /**
     * @var integer
     *
     * @Column(name="item_id", type="integer", nullable=true)
     */
    protected $itemId;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $creationDate;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": UNIX_TIMESTAMP()})
     */
    protected $modificationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


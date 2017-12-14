<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceSubscriptionsIncludedItems
 *
 * @Table(name="commerce_subscriptions_included_items")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class CommerceSubscriptionsIncludedItems extends EntityBase
{

    protected $fillable;
    /**
     * @var integer
     *
     * @Column(name="subscription_id", type="integer", nullable=false)
     */
    protected $subscription_id;

    /**
     * @var integer
     *
     * @Column(name="item_id", type="integer", nullable=true)
     */
    protected $item_id;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creation_date;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=false, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modification_date;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


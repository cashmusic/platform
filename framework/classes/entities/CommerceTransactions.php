<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceTransactions
 *
 * @Table(name="commerce_transactions")
 * @Entity
 */
class CommerceTransactions extends EntityBase
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
     * @Column(name="connection_id", type="integer", nullable=false)
     */
    protected $connectionId;

    /**
     * @var string
     *
     * @Column(name="connection_type", type="string", length=255, nullable=false)
     */
    protected $connectionType;

    /**
     * @var string
     *
     * @Column(name="service_timestamp", type="string", length=255, nullable=false)
     */
    protected $serviceTimestamp;

    /**
     * @var string
     *
     * @Column(name="service_transaction_id", type="string", length=255, nullable=false)
     */
    protected $serviceTransactionId = '';

    /**
     * @var string
     *
     * @Column(name="data_sent", type="text", length=65535, nullable=false)
     */
    protected $dataSent;

    /**
     * @var string
     *
     * @Column(name="data_returned", type="text", length=65535, nullable=false)
     */
    protected $dataReturned;

    /**
     * @var boolean
     *
     * @Column(name="successful", type="boolean", nullable=true)
     */
    protected $successful = '0';

    /**
     * @var string
     *
     * @Column(name="gross_price", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $grossPrice;

    /**
     * @var string
     *
     * @Column(name="service_fee", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $serviceFee;

    /**
     * @var string
     *
     * @Column(name="currency", type="string", length=255, nullable=true)
     */
    protected $currency = 'USD';

    /**
     * @var string
     *
     * @Column(name="status", type="string", length=255, nullable=true)
     */
    protected $status = 'abandoned';

    /**
     * @var string
     *
     * @Column(name="parent", type="string", length=255, nullable=false)
     */
    protected $parent = 'order';

    /**
     * @var integer
     *
     * @Column(name="parent_id", type="integer", nullable=false)
     */
    protected $parentId = '0';

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=false)
     */
    protected $creationDate = '0';

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true)
     */
    protected $modificationDate = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


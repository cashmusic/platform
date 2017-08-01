<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceTransactions
 *
 * @Table(name="commerce_transactions")
 * @Entity @HasLifecycleCallbacks */
class CommerceTransaction extends EntityBase
{

    protected $fillable = ['user_id', 'connection_id', 'connection_type', 'service_timestamp', 'service_transaction_id', 'data_sent', 'data_returned', 'successful', 'gross_price', 'service_fee', 'currency', 'status', 'parent', 'parent_id'];
    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

    /**
     * @var integer
     *
     * @Column(name="connection_id", type="integer", nullable=false)
     */
    protected $connection_id;

    /**
     * @var string
     *
     * @Column(name="connection_type", type="string", length=255, nullable=false)
     */
    protected $connection_type;

    /**
     * @var string
     *
     * @Column(name="service_timestamp", type="string", length=255, nullable=false)
     */
    protected $service_timestamp;

    /**
     * @var string
     *
     * @Column(name="service_transaction_id", type="string", length=255, nullable=false)
     */
    protected $service_transaction_id = '';

    /**
     * @var string
     *
     * @Column(name="data_sent", type="json_array", length=65535, nullable=false)
     */
    protected $data_sent;

    /**
     * @var string
     *
     * @Column(name="data_returned", type="json_array", length=65535, nullable=false)
     */
    protected $data_returned;

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
    protected $gross_price;

    /**
     * @var string
     *
     * @Column(name="service_fee", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $service_fee;

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
    protected $parent_id = '0';

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
    protected $modification_date = '0';

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;

}


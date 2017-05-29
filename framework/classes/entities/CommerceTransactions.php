<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * CommerceTransactions
 *
 * @ORM\Table(name="commerce_transactions")
 * @ORM\Entity
 */
class CommerceTransactions extends EntityBase
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
     * @ORM\Column(name="connection_id", type="integer", nullable=false)
     */
    protected $connectionId;

    /**
     * @var string
     *
     * @ORM\Column(name="connection_type", type="string", length=255, nullable=false)
     */
    protected $connectionType;

    /**
     * @var string
     *
     * @ORM\Column(name="service_timestamp", type="string", length=255, nullable=false)
     */
    protected $serviceTimestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="service_transaction_id", type="string", length=255, nullable=false)
     */
    protected $serviceTransactionId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="data_sent", type="text", length=65535, nullable=false)
     */
    protected $dataSent;

    /**
     * @var string
     *
     * @ORM\Column(name="data_returned", type="text", length=65535, nullable=false)
     */
    protected $dataReturned;

    /**
     * @var boolean
     *
     * @ORM\Column(name="successful", type="boolean", nullable=true)
     */
    protected $successful = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="gross_price", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $grossPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="service_fee", type="decimal", precision=9, scale=2, nullable=true)
     */
    protected $serviceFee;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=255, nullable=true)
     */
    protected $currency = 'USD';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    protected $status = 'abandoned';

    /**
     * @var string
     *
     * @ORM\Column(name="parent", type="string", length=255, nullable=false)
     */
    protected $parent = 'order';

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=false)
     */
    protected $parentId = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=false)
     */
    protected $creationDate = '0';

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


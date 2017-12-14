<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemSettings
 *
 * @Table(name="system_settings")
 * @Entity(repositoryClass="CASHMusic\Entities\CASHEntityRepository") @HasLifecycleCallbacks */
class SystemSettings extends EntityBase
{

    protected $fillable = ['type', 'value', 'user_id', 'creation_date', 'modification_date'];
    /**
     * @var string
     *
     * @Column(name="type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @Column(name="value", type="text", length=65535, nullable=false)
     */
    protected $value;

    /**
     * @var integer
     *
     * @Column(name="user_id", type="integer", nullable=false)
     */
    protected $user_id;

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

    // we need to hack this for this table because the values are inconsistent--- some are JSON, some are strings/integers. 1000x bummer.
    public function getValueAttribute() {
        if ($value = json_decode($this->value, true)) {
            return $value;
        } else {
            return trim($this->value, '""');
        }
    }

    public function setValueAttribute($value) {
        if (is_array($value) || is_cash_model($value)) {
            if (is_cash_model($value)) $value = $value->toArray();
            $this->value = json_encode($value);
        } else {
            $this->value = trim($value);
        }
    }

}


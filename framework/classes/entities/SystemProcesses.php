<?php
namespace CASHMusic\Entities;


use Doctrine\ORM\Mapping as ORM;

/**
 * SystemProcesses
 *
 * @Table(name="system_processes")
 * @Entity @HasLifecycleCallbacks */
class SystemProcesses extends EntityBase
{

    protected $fillable;
    /**
     * @var string
     *
     * @Column(name="data", type="blob", length=16777215, nullable=true)
     */
    protected $data;

    /**
     * @var integer
     *
     * @Column(name="job_id", type="integer", nullable=false)
     */
    protected $jobId;

    /**
     * @var string
     *
     * @Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var integer
     *
     * @Column(name="modification_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $modificationDate;

    /**
     * @var integer
     *
     * @Column(name="creation_date", type="integer", nullable=true, options={"default": "UNIX_TIMESTAMP()"})
     */
    protected $creationDate;

    /** @Id @Column(type="integer") @GeneratedValue(strategy="AUTO") **/
    protected $id;
}


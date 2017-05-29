<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SystemProcesses
 *
 * @ORM\Table(name="system_processes")
 * @ORM\Entity
 */
class SystemProcesses
{
    /**
     * @var string
     *
     * @ORM\Column(name="data", type="blob", length=16777215, nullable=true)
     */
    private $data;

    /**
     * @var integer
     *
     * @ORM\Column(name="job_id", type="integer", nullable=false)
     */
    private $jobId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="modification_date", type="integer", nullable=true)
     */
    private $modificationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     */
    private $creationDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;


    /**
     * Set data
     *
     * @param string $data
     *
     * @return SystemProcesses
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set jobId
     *
     * @param integer $jobId
     *
     * @return SystemProcesses
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;

        return $this;
    }

    /**
     * Get jobId
     *
     * @return integer
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return SystemProcesses
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set modificationDate
     *
     * @param integer $modificationDate
     *
     * @return SystemProcesses
     */
    public function setModificationDate($modificationDate)
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    /**
     * Get modificationDate
     *
     * @return integer
     */
    public function getModificationDate()
    {
        return $this->modificationDate;
    }

    /**
     * Set creationDate
     *
     * @param integer $creationDate
     *
     * @return SystemProcesses
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return integer
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}


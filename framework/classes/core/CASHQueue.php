<?php

class CASHQueue extends CASHData
{
    public $job_id;
    private $user_id, $type;

    public function __construct($user_id, $type)
    {
        $this->user_id = $user_id;
        $this->type = $type;

        if (!$this->db) $this->connectDB();

        $this->createJob();
    }

    /**
     * Create a job and return an id--- fires when class is constructed because it's cleaner to call CASHQueue that way.
     * Daemon calls will be made statically, so ¯\_(ツ)_/¯
     *
     * @return bool
     */
    public function createJob() {

        // create a job and set the id to this instance, or die trying
        if (!$this->job_id = $this->db->setData(
            'system_jobs',
            array(
                'user_id' 		=> $this->user_id,
                'type'		    => $this->type
            )
        )) {

            if (CASH_DEBUG) {
                error_log("CASHQueue->createJob failed " . $this->job_id);
            }
            return false;
        }
    }

    public function createSystemProcess($data, $name) {

        if (!$process_id = $this->db->setData(
            'system_processes',
            array(
                'job_id'        => $this->job_id,
                'data' 		    => json_encode($data),
                'name'		    => $name
            )
        )) {
            return false;
        }

        return $process_id;
    }

}
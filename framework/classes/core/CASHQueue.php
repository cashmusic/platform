<?php

class CASHQueue extends CASHData
{
    public $job_id;
    private $user_id, $table_id, $type;

    public function __construct($user_id, $table_id, $type)
    {
        $this->user_id = $user_id;
        $this->type = $type;
        $this->table_id = $table_id;

        if (!$this->db) $this->connectDB();
        
        // lookup to see if we've got a job
        if (!$this->getSystemJob()) $this->createSystemJob();
    }

    /**
     * Create a job and return an id--- fires when class is constructed because it's cleaner to call CASHQueue that way.
     * Daemon calls will be made statically, so ¯\_(ツ)_/¯
     *
     * @return bool
     */
    public function createSystemJob() {

        // create a job and set the id to this instance, or die trying
        if (!$this->job_id = $this->db->setData(
            'jobs',
            array(
                'user_id' 		=> $this->user_id,
                'table_id'      => $this->table_id,
                'type'		    => $this->type
            )
        )) {

            if (CASH_DEBUG) {
                error_log("CASHQueue->createJob failed " . $this->job_id);
            }
            return false;
        }
    }

    public function getSystemJob() {

        //TODO: maybe get highest ID in case there are multiples
        $condition = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'table_id' => [
                'condition' => '=',
                'value' => $this->table_id
            ],
            'type' => [
                'condition' => '=',
                'value' => $this->type
            ]
        ];

        if (!$system_job = $this->db->getData(
            'jobs', '*', $condition
        )) {
            return false;
        } else {

            $this->job_id = $system_job[0]['id'];

            return true;
        }
    }

    public function createSystemProcess($data, $name) {

        if (!$process_id = $this->db->setData(
            'processes',
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

    public function getSystemProcessesByJob() {
        $condition = [
            'job_id' => [
                'condition' => '=',
                'value' => $this->job_id
            ]
        ];

        if (!$system_processes = $this->db->getData(
            'processes', '*', $condition
        )) {
            return false;
        } else {
            return $system_processes;
        }
    }

    public function deleteSystemProcess($process_id, $job_id) {
        $conditions = [
            'id' => [
                'condition' => '=',
                'value' => $process_id
            ],
            'job_id' => [
                'condition' => '=',
                'value' => $job_id
            ]
        ];

        if (!$this->db->deleteData(
            'processes', $conditions
        )) {
            return false;
        }

        return true;
    }

    public function deleteSystemJob() {
        $conditions = [
            'id' => [
                'condition' => '=',
                'value' => $this->job_id
            ],
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ]
        ];

        if (!$this->db->deleteData(
            'jobs', $conditions
        )) {
            return false;
        }

        return true;
    }

}
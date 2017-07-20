<?php

namespace CASHMusic\Core;

use CASHMusic\Core\CASHData as CASHData;
use CASHMusic\Entities\SystemJob;
use CASHMusic\Entities\SystemProcess;

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

        if (CASH_DEBUG) {
            error_log("CASHQueue constructed");
        }

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
        if ($system_job = $this->orm->create(SystemJob::class, [
            'user_id' 		=> $this->user_id,
            'table_id'      => $this->table_id,
            'type'		    => $this->type
        ])) {
            $this->job_id = $system_job->id;

            return true;
        }

        return false;
    }

    public function getSystemJob() {

        //TODO: maybe get highest ID in case there are multiples
        $conditions = [
            'user_id' => $this->user_id,
            'table_id' => $this->table_id,
            'type' => $this->type
        ];

       if ($system_job = $this->orm->findWhere(SystemJob::class, $conditions, ['id' => 'DESC'], 1)) {
           $this->job_id = $system_job->id;
           return true;
       }

       return false;
    }

    public function createSystemProcess($data, $name) {

        if ($system_process = $this->orm->create(SystemProcess::class, [
            'job_id'        => $this->job_id,
            'data' 		    => $data,
            'name'		    => $name
        ])) {
            return $system_process->id;
        }

        return false;
    }

    public function getSystemProcessesByJob() {

        if ($system_processes = $this->orm->findWhere(SystemProcess::class, ['job_id' => $this->job_id])) {
            return $system_processes;
        }

        return false;
    }

    public function deleteSystemProcess($process_id, $job_id) {

        if ($this->db->table('system_processes')->where(['id'=>$process_id, 'job_id'=>$job_id])->delete()) {
            return true;
        }

        return false;
    }

    public function deleteSystemJob() {

        if ($this->db->table('system_jobs')->where(['id'=>$this->job_id, 'user_id'=>$this->user_id])->delete()) {
            return true;
        }

        return false;
    }

}
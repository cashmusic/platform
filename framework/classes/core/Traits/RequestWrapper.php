<?php

namespace CASHMusic\Core\Traits;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;

/**
 * Created by PhpStorm.
 * User: tomfilepp
 * Date: 12/24/17
 * Time: 5:30 PM
 */
trait RequestWrapper
{
    private $request_plant, $request_verb, $request_data;

    public function request($plant) {
        $this->request_plant = $plant;
        return $this;
    }

    public function action($verb) {
        $this->request_verb = $verb;
        return $this;
    }

    public function with($data) {
        $this->request_data = $data;
        return $this;
    }

    public function get($argument=false) {

        $pdo = $this->getPDOConnection();

        $request_array = [
            'cash_request_type' => $this->request_plant,
            'cash_action' => $this->request_verb
        ];

        if (isset($this->request_data)) $request_array = array_merge($request_array, $this->request_data);

        try {
            $request = new CASHRequest($request_array, 'direct', false, false, false, $pdo);
        } catch (\Exception $e) {
            return false;
        }

        //TODO: right now we're passing the whole request back...
        //TODO: honestly we should only pass failures back with more info
        //TODO: otherwise WTF let's just give them the data...
        if (isset($request->response)) {
            return $request->response;
        }
        return false;
    }

    public function session($session_id=false) {

        $pdo = $this->getPDOConnection();

        try {
            $request = new CASHRequest(false, 'direct', false, false, false, $pdo);
        } catch (\Exception $e) {
            return false;
        }

        if (isset($request)) {
            $request->startSession($session_id);
            return $request;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function getPDOConnection()
    {
        $pdo = false;
        if (isset($this->db) && method_exists($this->db, "pdo")) {
            $pdo = $this->db->pdo();
        }
        return $pdo;
    }
}
<?php

class ExternalFulfillmentSeed extends SeedBase
{
    protected $user_id, $system_job_id, $fulfillment_job_id;
    private $uploaded_files, $raw_data, $parsed_data, $mappable_fields, $mapped_fields, $minimum_field_requirements, $queue;

    public function __construct($user_id)
    {

        $this->raw_data = [];
        $this->parsed_data = [];
        $this->mappable_fields = [];
        $this->has_minimal_mappable_fields = false;

        // default for kickstarter imports
        $this->mapped_fields = [
            'name'                => 'Shipping Name',
            'email'               => 'Email',
            'price'               => 'Pledge Amount',
            'notes'               => 'Notes',
            'shipping_address_1'  => 'Shipping Address 1',
            'shipping_address_2'  => 'Shipping Address 2',
            'shipping_city'       => 'Shipping City',
            'shipping_province'   => 'Shipping State',
            'shipping_postal'     => 'Shipping Postal',
            'shipping_country'    => 'Shipping Country Code'
        ];

        $this->minimum_field_requirements = [
            'name' => false,
            'email' => false
        ];

        $this->user_id = $user_id;

        if (!$this->db) $this->connectDB();

        if (CASH_DEBUG) {
            error_log("ExternalFulfillmentSeed loaded with user_id ".$this->user_id);
        }
    }

    public function processUpload($files) {

        $this->uploaded_files = $files;

        if (CASH_DEBUG) {
            error_log("processUpload called on ".count($this->uploaded_files['name'])." files.");
        }
        // loop through uploaded files
        for($i=0;$i<count($this->uploaded_files['name']);$i++) {

            // get file contents
            $file_contents = CASHSystem::getFileContents($this->uploaded_files['tmp_name'][$i]);


            if ($csv_to_array = CASHSystem::outputCSVToArray($file_contents)) {
                $this->raw_data[
                $this->uploaded_files['name'][$i]
                ] = $csv_to_array['array'];

                $this->mappable_fields = array_merge(
                    $this->mappable_fields,
                    $csv_to_array['unique_fields']
                );

            } else {
                return false;
            }

        }

        if ($this->checkMinimumMappableFields()) {
            // so we've ascertained this is a kickstarter import, so let's try to map these base fields
            $this->has_minimal_mappable_fields = true;
        }

        return $this;
    }

    public function checkMinimumMappableFields() {
        // we need to check if these CSVs have the structure we're expecting
        //TODO: this needs to be more dynamic

        if (
            in_array("Backer Name", $this->mappable_fields) ||
            in_array("Shipping Name", $this->mappable_fields)
        ) {
            $this->minimum_field_requirements['name'] = true;
        }

        if (
        in_array("Email", $this->mappable_fields)
        ) {
            $this->minimum_field_requirements['email'] = true;
        }

        // if we didn't find any of the fields we're looking for, we need to do this manually
        if (in_array(false, $this->minimum_field_requirements)) {
            return false;
        }

        return true;
    }

/*    public function standardizeOrderArray() {
        //var_dump($this->mappable_fields);
        // it's an array of CSV files; each CSV file has rows. so here we're looping through files, not rows of data
        foreach($this->raw_data as $csv_set) {
            // then here we're looping through rows in each file. each row is an order.
            foreach ($csv_set as $csv_row) {
                //var_dump($csv_row); // this is showing the right fields
                $row = [];
                // we have to map each field in each raw_data row to one big standardized array

                foreach ($this->mappable_fields as $mappable_key) {
                    // mappable field key exists in this row of raw data, so let's map it
                    $mappable_key = trim($mappable_key);
                    $row[$mappable_key] = "";

                    if (array_key_exists($mappable_key, $csv_row)) {
                        $row[$mappable_key] = $csv_row[$mappable_key];
                    }
                }

                //var_dump($row); // this is also showing the correct amount of fields
                $this->parsed_data[] = $row;
            }
            //var_dump($this->parsed_data); // this shows all rows, but it's missing a bunch of fields on a lot of the rows
        }

        if ($this->checkMinimumMappableFields()) {
            // this is a kickstarter import.
            $this->mapStandardFields();

        } else {
            // we need to map fields manually.
        }

        return $this;
    }*/

    public function createFulfillmentJob($asset_id, $name) {
        if (!$fulfillment_job = $this->db->setData(
            'external_fulfillment_jobs',
            array(
                'user_id'       => $this->user_id,
                'asset_id'      => $asset_id,
                'name'		    => $name,
                'mappable_fields'   => json_encode($this->mappable_fields),
                'has_minimum_mappable_fields'   => $this->has_minimal_mappable_fields
            )
        )) {
            return false;
        } else {
            return $fulfillment_job;
        }
    }

    public function getFulfillmentJobByUserId() {

        $condition = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ]
        ];

        if (!$fulfillment_job = $this->db->getData(
            'external_fulfillment_jobs', '*', $condition
        )) {
            return false;
        } else {

            // map some fields from the results
            $this->asset_id = $fulfillment_job[0]['asset_id'];
            $this->fulfillment_job_name = $fulfillment_job[0]['name'];
            $this->mappable_fields = json_decode($fulfillment_job[0]['mappable_fields']);
            $this->has_minimum_mappable_fields = (bool) $fulfillment_job[0]['has_minimum_mappable_fields'];
            $this->fulfillment_job_id = $fulfillment_job[0]['id'];

            return $this;
        }
    }

    public function createFulfillmentTier($process_id, $name, $data) {

        if (!$fulfillment_tier = $this->db->setData(
            'external_fulfillment_tiers',
            array(
                'system_job_id'        => $this->system_job_id,
                'fulfillment_job_id'    => $this->fulfillment_job_id,
                'process_id' 	=> $process_id,
                'user_id'       => $this->user_id,
                'name'		    => $name,
                'metadata'      => json_encode($data)
            )
        )) {
            return false;
        }

        return $fulfillment_tier;
    }

    public function processOrder($order, $tier_id) {
        $order_mapped = [];

        foreach($this->mapped_fields as $destination_field=>$source_field) {

            // we can deal with the minimum expected fields first, and go from there
            if (!empty($order[$source_field])) {
                $source = empty($order[$source_field]) ? '' : $order[$source_field];
            }

            // fallback if it's empty or not set
            if (empty($order[$source_field])) {

                // the ol' digital order switcheroo
                if ($source_field == "Shipping Name") {
                    $source = empty($order["Backer Name"]) ? '' : $order["Backer Name"];
                } else {
                    $source = "";
                }
            }

            // either way this is now mapped correctly
            $order_mapped[$destination_field] = $source;
        }

        // hack the system
        $order_mapped['notes'] = empty($order["Notes"]) ? '' : $order["Notes"];
        $order_mapped['order_data'] = json_encode($order);

        $order_mapped['tier_id'] = $tier_id;

        // create order
        $this->createOrder($order_mapped);
    }

    public function createOrder($order_details) {

        if (!$order = $this->db->setData(
            'external_fulfillment_orders', $order_details
        )) {
            return false;
        }

        return $this;
    }

    public function createJob() {

        if (CASH_DEBUG) {
            error_log("Called createOrders");
        }

        // create external fulfillment job (asset id, job name FPO)
        $this->fulfillment_job = $this->createFulfillmentJob(123, "Job name");

        // pass external fulfillment job id to a new cash queue job
        if ($this->queue = new CASHQueue(
                $this->user_id,
                $this->fulfillment_job,
                'external_fulfillment_jobs')
            ) {

            if (CASH_DEBUG) {
                error_log("New queue job created: ".$this->queue->job_id);
            }
            // insert raw data into system processes, per CSV; then use process id to insert into fulfillment jobs
            foreach ($this->raw_data as $filename => $tier) {

                $job_name = basename($filename, '.csv');
                $process_id = $this->queue->createSystemProcess(
                    $tier,                          // raw data, for parity
                    $job_name     // this could be anything, but naming the process by filename seems okay
                );

            }

            return $this;
        }

        return false;

    }

    public function createTiers() {

        // lookup fulfillment jobs per user id
        $this->getFulfillmentJobByUserId();

        // hit system jobs with table_id, type to get master job id
        if (!$this->queue = new CASHQueue(
            $this->user_id,
            $this->fulfillment_job_id,
            'external_fulfillment_jobs')
        ) {
            
            // there's no valid job id, brah
            return false;
        } else {
            $this->system_job_id = $this->queue->job_id;

            if (!$this->has_minimum_mappable_fields) {
                // we need to have them map shit
            }

            // get processes by the system job id, and loop through them if there are any
            if ($system_processes = $this->queue->getSystemProcessesByJob()) {
                foreach($system_processes as $process) {
                    // loop through system processes
                    if($data = json_decode($process['data'], true)) {

                        // create tiers
                        if($tier_id = $this->createFulfillmentTier($process['id'], $process['name'], $data)) {
                            //orders per tier

                            foreach ($data as $order) {
                                // loop through each order and store it in the database
                                $this->processOrder($order, $tier_id);
                            }

                        }

                        // if no errors, delete this system process
                        $this->queue->deleteSystemProcess($process['id'], $this->system_job_id);

                    }


                }
            }

            // delete the system job
            $this->queue->deleteSystemJob();

        }


    }

}
<?php

class ExternalFulfillmentSeed extends SeedBase
{
    protected $user_id;
    private $uploaded_files, $raw_data, $parsed_data, $mappable_fields, $mapped_fields, $minimum_field_requirements, $queue;

    public function __construct($user_id)
    {

        $this->raw_data = [];
        $this->parsed_data = [];
        $this->mappable_fields = [];
        $this->mapped_fields = [];
        $this->user_id = $user_id;

        if (!$this->db) $this->connectDB();

        $this->minimum_field_requirements = [
            'name' => false,
            'email' => false
        ];

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
            ];

        } else {
            // we need to map fields manually. :(
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

    public function createFulfillmentJob($process_id, $name) {

        if (!$fulfillment_job = $this->db->setData(
            'external_fulfillment_jobs',
            array(
                'job_id'        => $this->queue->job_id,
                'process_id' 	=> $process_id,
                'user_id'       => $this->user_id,
                'name'		    => $name
            )
        )) {
            return false;
        }

        return $this;
    }

    public function createOrder($order_details) {

        if (!$order = $this->db->setData(
            'external_fulfillment_orders', $order_details
        )) {
            return false;
        }

        return $this;
    }

    public function createOrders() {

        if (CASH_DEBUG) {
            error_log("Called createOrders");
        }

        if ($this->queue = new CASHQueue($this->user_id, 'external_fulfillment')) {

            if (CASH_DEBUG) {
                error_log("new queue job created: ".$this->queue->job_id);
            }
            // insert raw data into system processes, per CSV; then use process id to insert into fulfillment jobs
            foreach ($this->raw_data as $filename => $tier) {

                $job_name = basename($filename, '.csv');
                $process_id = $this->queue->createSystemProcess(
                    $tier,                          // raw data, for parity
                    $job_name     // this could be anything, but naming the process by filename seems okay
                );

                // fulfillment job
                $this->createFulfillmentJob($process_id, $job_name);

                // loop through each order and store it in the database
                foreach ($tier as $order) {

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
                    $order_mapped['job_id'] = $this->queue->job_id;

                    $order_mapped['user_id'] = $this->user_id;

                    $order_mapped['item_id'] = 1; //TODO: obviously FPO
                    $order_mapped['order_data'] = json_encode($order);

                    // create order
                    $this->createOrder($order_mapped);

                }
            }

            return $this;
        }

        return false;

    }

}
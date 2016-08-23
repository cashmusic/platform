<?php

class ExternalFulfillmentSeed extends SeedBase
{
    public $user_id, $system_job_id, $fulfillment_job, $job_name, $status, $queue;
    private $uploaded_files, $raw_data, $parsed_data, $mappable_fields, $mapped_fields, $minimum_field_requirements;

    public function __construct($user_id)
    {

        $this->raw_data = [];
        $this->parsed_data = [];
        $this->mappable_fields = [];
        $this->has_minimal_mappable_fields = false;

        // default for kickstarter imports
        $this->mapped_fields = [
            'name' => 'Shipping Name',
            'email' => 'Email',
            'price' => 'Pledge Amount',
            'notes' => 'Notes',
            'shipping_address_1' => 'Shipping Address 1',
            'shipping_address_2' => 'Shipping Address 2',
            'shipping_city' => 'Shipping City',
            'shipping_province' => 'Shipping State',
            'shipping_postal' => 'Shipping Postal',
            'shipping_country' => 'Shipping Country'
        ];

        $this->minimum_field_requirements = [
            'name' => false,
            'email' => false
        ];

        $this->user_id = $user_id;

        if (!$this->db) $this->connectDB();

        if (CASH_DEBUG) {
            error_log("ExternalFulfillmentSeed loaded with user_id " . $this->user_id);
        }
    }

    public function getUserJobs()
    {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ]/*,
            'status' => [
                'condition' => '=',
                'value' => 'processed'
            ]*/
        ];

        if (!$fulfillment_job = $this->db->getData(
            'external_fulfillment_jobs', '*', $conditions, false, 'id DESC'
        )
        ) {
            return false;
        } else {

            $user_jobs = [];

            // loop through each job found
            foreach ($fulfillment_job as $job) {
                $tiers = $this->getTiersByJobCount($job['id']);

                if ($tiers < 1) {
                    $tiers = false;
                }
                $job['tiers_count'] = $tiers;

                $user_jobs[] = $job;
            }

            return $user_jobs;
        }
    }

    public function getUserJobById($id)
    {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'id' => [
                'condition' => '=',
                'value' => $id
            ]
        ];

        if (!$fulfillment_job = $this->db->getData(
            'external_fulfillment_jobs', '*', $conditions
        )
        ) {
            return false;
        } else {

            $user_jobs = [];

            // loop through each job found
            foreach ($fulfillment_job as $job) {
                $tiers = $this->getTiersByJob($job['id']);

                if ($tiers < 1) {
                    $tiers = false;
                }

                $job['tiers'] = $tiers;
                $job['tiers_count'] = count($tiers);

                $user_jobs[] = $job;
            }

            return $user_jobs;
        }
    }

    public function getTiersByJob($job_id)
    {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'fulfillment_job_id' => [
                'condition' => '=',
                'value' => $job_id
            ]
        ];

        if (!$tiers = $this->db->getData(
            'CommercePlant_getExternalFulfillmentTiersAndOrderCount', false, $conditions
        )
        ) {
            return false;
        } else {
            return $tiers;
        }
    }

    public function getOrderCountByJob($job_id = false, $filter = false)
    {

        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'fulfillment_job_id' => [
                'condition' => '=',
                'value' => ($job_id) ? $job_id : $this->fulfillment_job
            ]
        ];

        // filter by some such thing (really, just complete, but left it open)
        if (is_array($filter)) {
            $conditions = array_merge([
                $filter['name'] => [
                    'condition' => '=',
                    'value' => ($filter['value']) ? 1 : 0
                ]
            ], $conditions);
        }

        error_log(
            print_r($conditions, true)
        );

        if (!$order_count = $this->db->getData(
            'CommercePlant_getOrderCountByJob', false, $conditions
        )
        ) {
            return false;
        } else {
            return $order_count[0]['total_orders'];
        }
    }

    public function getTiersByJobCount($job_id)
    {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'fulfillment_job_id' => [
                'condition' => '=',
                'value' => $job_id
            ]
        ];

        if (!$tiers = $this->db->getData(
            'external_fulfillment_tiers', 'count(*) as total_tiers', $conditions
        )
        ) {
            return false;
        } else {
            return $tiers[0]['total_tiers'];
        }
    }

    public function parseUpload($files)
    {

        $this->uploaded_files = $files;

        if (CASH_DEBUG) {
            error_log("parseUpload called (" . count($this->uploaded_files['name']) . " files.)");
        }

        for ($i = 0; $i < count($this->uploaded_files['name']); $i++) {

            // get file contents
            $file_contents = CASHSystem::getFileContents($this->uploaded_files['tmp_name'][$i]);


            if ($csv_to_array = CASHSystem::outputCSVToArray($file_contents)) {
                $this->raw_data[$this->uploaded_files['name'][$i]] = $csv_to_array['array'];

                $this->mappable_fields = array_merge(
                    $this->mappable_fields,
                    $csv_to_array['unique_fields']
                );
                return $this;
            } else {

                // we should throw an exception here, actually
                return false;
            }

        }

        if ($this->checkMinimumMappableFields()) {
            // so we've ascertained this is a kickstarter import, so let's try to map these base fields
            $this->has_minimal_mappable_fields = true;
        }

        return $this;
    }

    public function checkMinimumMappableFields()
    {
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

    public function createFulfillmentJob($asset_id, $name, $description = "")
    {
        if (!$fulfillment_job = $this->db->setData(
            'external_fulfillment_jobs',
            array(
                'user_id' => $this->user_id,
                'asset_id' => $asset_id,
                'name' => $name,
                'description' => $description,
                'mappable_fields' => json_encode($this->mappable_fields),
                'has_minimum_mappable_fields' => $this->has_minimal_mappable_fields
            )
        )
        ) {
            return false;
        } else {
            error_log(
                'createFulfillmentJobbyjob ' . print_r($fulfillment_job, true)
            );

            $this->fulfillment_job = $fulfillment_job;
            return true;
        }
    }

    public function updateFulfillmentJob($values, $id = false)
    {

        // allows us to manually override
        if (!$id) {
            $id = $this->fulfillment_job;
        } else {
            // trickle down to the next method
            $this->fulfillment_job = $id;
        }

        if (!empty($values)) {

            $conditions = [
                'user_id' => [
                    'condition' => '=',
                    'value' => $this->user_id
                ],
                'id' => [
                    'condition' => '=',
                    'value' => $id
                ]
            ];

            $this->db->setData(
                'external_fulfillment_jobs',
                $values,
                $conditions
            );
        }

        return $this;
    }

    public function getFulfillmentJobByUserId($status)
    {

        if (!$status) {
            $status = 'created';
        }

        if (is_array($status)) {

            $condition = [
                'user_id' => [
                    'condition' => '=',
                    'value' => $this->user_id
                ],
                'status' => [
                    'condition' => 'IN',
                    'value' => $status
                ]
            ];
        } else {

            $conditions = [
                'user_id' => [
                    'condition' => '=',
                    'value' => $this->user_id
                ],
                'status' => [
                    'condition' => '=',
                    'value' => $status
                ]
            ];
        }
        error_log("getFulfillmentJobByUserId .......");

        if (!$fulfillment_job = $this->db->getData(
            'external_fulfillment_jobs', '*', $conditions, 1, 'id DESC'
        )
        ) {
            error_log("---- returned false");
            return false;
        } else {
            error_log("---- returned true");
            // map some fields from the results
            $this->asset_id = $fulfillment_job[0]['asset_id'];
            $this->job_name = $fulfillment_job[0]['name'];
            $this->mappable_fields = json_decode($fulfillment_job[0]['mappable_fields']);
            $this->has_minimum_mappable_fields = (bool)$fulfillment_job[0]['has_minimum_mappable_fields'];
            $this->fulfillment_job = $fulfillment_job[0]['id'];
            $this->status = $fulfillment_job[0]['status'];

            error_log("---- fulfillment job: " . $this->fulfillment_job);

            return true;
        }
    }

    public function createFulfillmentTier($process_id, $name, $upc, $data)
    {

        if (!$fulfillment_tier = $this->db->setData(
            'external_fulfillment_tiers',
            array(
                'system_job_id' => $this->system_job_id,
                'fulfillment_job_id' => $this->fulfillment_job,
                'process_id' => $process_id,
                'user_id' => $this->user_id,
                'name' => $name,
                'upc' => $upc,
                'metadata' => json_encode($data)
            )
        )
        ) {
            return false;
        }

        return $fulfillment_tier;
    }

    public function processOrder($order, $tier_id)
    {
        $order_mapped = [];

        foreach ($this->mapped_fields as $destination_field => $source_field) {

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

    public function createOrder($order_details)
    {

        if (!$order_id = $this->db->setData(
            'external_fulfillment_orders', $order_details
        )
        ) {
            return false;
        }

        $this->generateDownloadCode($order_id);

        return $this;
    }

    public function createOrContinueJob($status = false)
    {

        if (CASH_DEBUG) {
            error_log("Called createOrContinueJob");
        }

        if ($this->getFulfillmentJobByUserId($status)) {

            $this->createOrGetSystemJob();

            if (!empty($_REQUEST['fulfillment_name'])) {
                // just in case this is one of those stray
                $job_name = $_REQUEST['fulfillment_name'] ? $_REQUEST['fulfillment_name'] : "";
                $description = $_REQUEST['fulfillment_description'] ? $_REQUEST['fulfillment_description'] : "";

                $this->updateFulfillmentJob([
                    'name' => $job_name,
                    'description' => $description
                ]);

                $this->job_name = $job_name;
            }

            error_log("### existing fulfillment job " . $this->job_name);

            return $this;
        } else {
            // create external fulfillment job (asset id, job name FPO)
            $job_name = $_REQUEST['fulfillment_name'] ? $_REQUEST['fulfillment_name'] : "";
            $description = $_REQUEST['fulfillment_description'] ? $_REQUEST['fulfillment_description'] : "";

            $this->createFulfillmentJob(0, $job_name, $description);
            $this->createOrGetSystemJob();

            $this->status = "created";
        }

        $this->job_name = $job_name;

        return $this;

    }

    public function createOrGetSystemJob()
    {
        // get or create cash queue job object
        if ($this->queue = new CASHQueue(
            $this->user_id,
            $this->fulfillment_job,
            'external_fulfillment_jobs')
        ) {

            if (CASH_DEBUG) {
                error_log("New queue job created: " . $this->queue->job_id);
            }


        } else {
            // return an error
        }
    }

    public function createJobProcesses()
    {
        // insert raw data into system processes, per CSV; then use process id to insert into fulfillment jobs
        foreach ($this->raw_data as $filename => $tier) {

            $this->queue->createSystemProcess(
                $tier,                          // raw data, for parity
                basename($filename, '.csv')     // this could be anything, but naming the process by filename seems okay
            );
        }

        return $this;
    }

    public function getJobProcesses()
    {
        if (!$processes = $this->queue->getSystemProcessesByJob()) {
            return false;
        }

        return $processes;
    }

    public function updateFulfillmentJobStatus($status)
    {

        $condition = array(
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'id' => [
                'condition' => '=',
                'value' => $this->fulfillment_job
            ],
            'status' => [
                'condition' => '=',
                'value' => $this->status
            ]
        );

        if (!$fulfillment_tier = $this->db->setData(
            'external_fulfillment_jobs',
            array(
                'status' => $status
            ),
            $condition

        )
        ) {
            return false;
        }

        $this->status = $status;

        return $this;
    }

    public function createTiers()
    {

        // hit system jobs with table_id, type to get master job id
        if (!$this->queue) {

            // there's no valid queue object, which means something went wrong when we tried to load or create
            error_log("no valid queue object");
            return false;
        } else {
            error_log("valid queue object");
            $this->system_job_id = $this->queue->job_id;

            if (!$this->has_minimum_mappable_fields) {
                // we need to have them map shit
            }

            // get processes by the system job id, and loop through them if there are any
            if ($system_processes = $this->queue->getSystemProcessesByJob()) {
                foreach ($system_processes as $process) {
                    // loop through system processes
                    if ($data = json_decode($process['data'], true)) {

                        // create tiers
                        if ($tier_id = $this->createFulfillmentTier($process['id'], $process['name'], '', $data)) {
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

        return $this;

    }

    /**
     * Update tiers on existing job, from the details page
     *
     * @return $this|bool
     */
    public function updateTiers()
    {

        if (!empty($_REQUEST['tier_name']) && count($_REQUEST['tier_name']) > 0) {

            foreach ($_REQUEST['tier_name'] as $tier_id => $tier_name) {
                // update tier
                $tier_name = isset($_REQUEST['tier_name'][$tier_id])
                    ? $_REQUEST['tier_name'][$tier_id] : $tier_name;

                $upc = isset($_REQUEST['tier_upc'][$tier_id])
                    ? $_REQUEST['tier_upc'][$tier_id] : "";

                $physical = isset($_REQUEST['tier_physical'][$tier_id])
                    ? $_REQUEST['tier_physical'][$tier_id] : 0;

                $shipped = isset($_REQUEST['tier_shipped'][$tier_id])
                    ? time() : 0;

                $conditions = [
                    'user_id' => [
                        'condition' => '=',
                        'value' => $this->user_id
                    ],
                    'id' => [
                        'condition' => '=',
                        'value' => $this->fulfillment_job
                    ],
                    'id' => [
                        'condition' => '=',
                        'value' => $tier_id
                    ]
                ];

                $this->db->setData(
                    'external_fulfillment_tiers',
                    [
                        'name' => $tier_name,
                        'upc' => $upc,
                        'physical' => $physical,
                        'shipped' => $shipped
                    ],
                    $conditions

                );

                // we also want to mark all orders inside this tier as completed,
                // with the timestamp for reporting (assuming it's shipped)
                if (!empty($shipped)) {
                    $conditions = [
                        'complete' => [
                            'condition' => '=',
                            'value' => 0
                        ],
                        'tier_id' => [
                            'condition' => '=',
                            'value' => $tier_id
                        ]
                    ];

                    $this->db->setData(
                        'external_fulfillment_orders',
                        [
                            'complete' => time()
                        ],
                        $conditions

                    );
                }

            }

        }

        return $this;

    }

    public function deleteJob($job_id)
    {

        // get tiers for this job
        if ($tiers = $this->getTiersByJob($job_id)) {
            // loop through tiers and delete orders
            foreach ($tiers as $tier) {

                // delete orders
                $this->db->deleteData(
                    'external_fulfillment_orders', [
                        'tier_id' => [
                            'condition' => '=',
                            'value' => $tier['id']
                        ]
                    ]
                );
            }
        }

        // delete tiers
        $this->db->deleteData(
            'external_fulfillment_tiers', [
                'fulfillment_job_id' => [
                    'condition' => '=',
                    'value' => $job_id
                ]
            ]
        );

        // delete job
        $this->db->deleteData(
            'external_fulfillment_jobs', [
                'id' => [
                    'condition' => '=',
                    'value' => $job_id
                ]
            ]
        );
    }

    /**
     * Get all orders since timestamp
     *
     * @param $timestamp
     */
    public static function getOrders($timestamp = 0, $physical = true)
    {

        $conditions = [
            'creation_date' => [
                'condition' => '>',
                'value' => $timestamp
            ],
            'physical' => [
                'condition' => '=',
                'value' => ($physical) ? 1 : 0
            ]
        ];

        $data_connection = new CASHRequest(null);

        if (!$data_connection->db) $data_connection->connectDB();

        // we're only getting stuff newer than $timestamp, and also where tier upc IS NOT NULL
        $orders = $data_connection->db->getData(
            'CommercePlant_getExternalFulfillmentOrdersByTimestamp', false, $conditions
        );

        return $orders;
    }

    /**
     * @param $order_id
     */
    public function generateDownloadCode($order_id)
    {
        if (!$add_request = new CASHRequest(
            array(
                'cash_request_type' => 'system',
                'cash_action' => 'addlockcode',
                'scope_table_alias' => 'external_fulfillment_orders',
                'scope_table_id' => $order_id
            )
        )
        ) {
            return false;
        }

        return true;
    }
    
    public function getBackersForJob($fulfillment_job_id) {
        $conditions = [
            'user_id' => [
                'condition' => '=',
                'value' => $this->user_id
            ],
            'fulfillment_job_id' => [
                'condition' => '=',
                'value' => $fulfillment_job_id
            ]
        ];

        if (!$backers = $this->db->getData(
            'CommercePlant_getExternalFulfillmentBackersByJob', false, $conditions
        )
        ) {
            return false;
        } else {
            return $backers;
        }
    }

}
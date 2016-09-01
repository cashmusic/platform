<?php

/**
 * Commerce external fulfillment index controller
 * All we're doing here is showing the existing jobs and giving them a "create job" button
 */

// get action from $_REQUEST, parameter, or just show the index template by default

if (!empty($request_parameters[0])) {
    $action = $request_parameters[0];
} else {
    $action = $_REQUEST['action'] ? $_REQUEST['action'] : "show_index";
}



/**
 * Behind the scenes controller actions
 */

// create a fulfillment seed with the effective user id
$user_id = AdminHelper::getPersistentData('cash_effective_user');
$external_fulfillment = new ExternalFulfillmentSeed();


if ($action == "soundscan") {
    $orders = $external_fulfillment->getOrders(1, time(), false);

    $soundscan = new SoundScanSeed(
        $orders, // upc, zip
        date("ymd", strtotime("Mon, 29 Aug 2016 11:59 PM America/New_York")),    // 12345
        "digital"
    );

    $soundscan->createReport()
        ->sendReport();
}

if ($action == "do_create") {
    // create the fulfillment job
    
    $external_fulfillment->createOrContinueJob();

    // set the view to show upload dialog
    $action = "show_upload";
}

if ($action == "do_upload") {
    // process uploads one by one; we're not setting a template here
    // because we're going to have it redirect on completion only

    if (!empty($_FILES['csv_upload'])) {

        $external_fulfillment
            ->createOrContinueJob("created")    // only grab it if it has status 'created'
            ->parseUpload($_FILES['csv_upload'])
            ->createJobProcesses();

    } else {
        // there's an issue, we're do_uploading without an upload
    }

}

if ($action == "do_process" || $action == "process") {
    // we're renaming each tier, and actually doing the tier to order conversion here
    // attaching a release asset for fulfillment

    $update = false;

/*    if (!empty($_REQUEST['item_fulfillment_asset'])) {
        $update = ['asset_id' => $_REQUEST['item_fulfillment_asset']];
    }*/

    $external_fulfillment
        ->createOrContinueJob("created")    // only grab the job if it's status 'process'
        //->updateFulfillmentJob($update)
        ->createTiers()
        ->updateFulfillmentJobStatus("pending");

    // set the view to the job detail, because we're done
    $action = "show_asset";
}

if ($action == "do_mailing") {

    if (!empty($_REQUEST['fulfillment_job_id']) &&
        !empty($_REQUEST['email_subject']) &&
        !empty($_REQUEST['email_message']) &&
        !empty($_REQUEST['email_url'])
        ) {
        //TODO: this should probably all be in a method

        $backers = $external_fulfillment->getBackersForJob($_REQUEST['fulfillment_job_id']);

        //$backers = array_slice($backers, 0, 500);

        // remove trailing slash from URLs
        $email_url = rtrim($_REQUEST['email_url'], "/");

        $global_merge_vars = [
            [
                'name' => 'url',
                'content' => $email_url
            ]
        ];

        // let's break this up into 1000 at a time to make sure we don't overload the mandrill API
        $chunky_backers = array_chunk($backers, 1000);

        $html_message = CASHSystem::parseMarkdown($_REQUEST['email_message']);
        $html_message .= "\n\n" . '<p><b><a href="*|URL|*?code=*|CODE|*&handlequery=1">Download</a></b></p>';

        $subject = trim($_REQUEST['email_subject']);

        foreach ($chunky_backers as $backers) {
            $recipients = [];
            $merge_vars = [];

            foreach ($backers as $backer) {

                if ($backer['email'] != "") {
                    $recipients[] = [
                        'email' => $backer['email'],
                        'name' => $backer['name']
                    ];

                    $merge_vars[] = [
                        'rcpt' => $backer['email'],
                        'vars' => [
                            [
                                'name' => 'code',
                                'content' => $backer['lockcode']
                            ]
                        ]
                    ];
                }
            }

            CASHSystem::sendMassEmail(
                $user_id,
                $subject,
                $recipients,
                $html_message,
                $subject,
                $global_merge_vars,
                $merge_vars,
                false,
                true);

        }

        $external_fulfillment
            ->createOrContinueJob(["pending", "sent"])
            ->updateFulfillmentJobStatus("sent");
    }

    $action = "show_index";
}

// if we've got this key then we need to override--- not really a better way to retain the URI and do this
if ($action == "detail" && !empty($_REQUEST['fulfillment_job_id'])) $action = "do_change";

if ($action == "do_change") {
    // we're renaming each tier, and actually doing the tier to order conversion here
    // attaching a release asset for fulfillment

    if (!empty($_REQUEST['fulfillment_job_id'])) {

        $id = $_REQUEST['fulfillment_job_id'];

        $update = [];

        if (!empty($_REQUEST['item_fulfillment_asset'])) {
            $update = array_merge($update, ['asset_id' => $_REQUEST['item_fulfillment_asset']]);
        }

        if (!empty($_REQUEST['fulfillment_job_name'])) {
            $update = array_merge($update, ['name' => $_REQUEST['fulfillment_job_name']]);
        }

        if (!empty($_REQUEST['fulfillment_job_description'])) {
            $update = array_merge($update, ['description' => $_REQUEST['fulfillment_job_description']]);
        }

        $external_fulfillment
            ->updateFulfillmentJob($update, $id)
            ->updateTiers();
    }


    // set the view to the job detail, because we're done
    $action = "show_detail";
}

if ($action == "do_delete" || $action == "delete") {

    if ($request_parameters[0] == "delete" &&
        is_numeric($request_parameters[1])
    ) {

        $id = $request_parameters[1];

        $external_fulfillment->deleteJob($id);
    }

    $action = "show_index";
}

/**
 * View switch
 */
if ($action == "show_index") {

    // Any mass mailing connection present?
    $cash_admin->page_data['mass_connection'] = AdminHelper::getConnectionsByScope('mass_email') || $external_fulfillment->getUserJobs() != false;

    // If no mass mailing connection found prompt add connection
    if (!$cash_admin->page_data['mass_connection']) {
    
        $page_data_object = new CASHConnection(AdminHelper::getPersistentData('cash_effective_user'));
    		$settings_types_data = $page_data_object->getConnectionTypes('mass_email');

        $all_services = array();
        $typecount = 1;
        foreach ($settings_types_data as $key => $data) {
        	if ($typecount % 2 == 0) {
        		$alternating_type = true;
        	} else {
        		$alternating_type = false;
        	}
        	if (file_exists(ADMIN_BASE_PATH.'/assets/images/settings/' . $key . '.png')) {
        		$service_has_image = true;
        	} else {
        		$service_has_image = false;
        	}
        	if (in_array($cash_admin->platform_type, $data['compatibility'])) {
        		$all_services[] = array(
        			'key' => $key,
        			'name' => $data['name'],
        			'description' => $data['description'],
        			'link' => $data['link'],
        			'alternating_type' => $alternating_type,
        			'service_has_image' => $service_has_image
        		);
        		$typecount++;
        	}
        }
        $cash_admin->page_data['all_services'] = new ArrayIterator($all_services);
    } 

    // If mass mailing connection found show existing jobs, and a create new job button    
    else {
    $cash_admin->page_data['user_jobs'] = $external_fulfillment->getUserJobs();
    }
  
    // set index view
    $cash_admin->setPageContentTemplate('commerce_externalfulfillment_index');
}

if ($action == "show_create" || $action == "create") {
    // initial create job form
    $cash_admin->setPageContentTemplate('commerce_externalfulfillment_create');
}

if ($action == "show_upload") {
    // upload files
    // set whatever values we need for the template
    $cash_admin->page_data['job_name'] = $external_fulfillment->job_name;

    $cash_admin->setPageContentTemplate('commerce_externalfulfillment_upload');
}

if ($action == "show_asset") {
    $cash_admin->page_data['job_name'] = $external_fulfillment->job_name;
    $cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',false,$cash_admin->getAllFavoriteAssets(),true);
    $cash_admin->page_data['id'] = $external_fulfillment->fulfillment_job; // for redirect purposes

    $cash_admin->setPageContentTemplate('commerce_externalfulfillment_asset');
}

if ($action == "show_process") {

    // this step we need to load the job manually here, because of the way the view is called

    $external_fulfillment
        ->createOrContinueJob(["created", "pending"])   //
        ->updateFulfillmentJobStatus("pending");        // mark this as ready to go, to be processed

    // load pending processes for this job and list them

    // set whatever values we need for the template
    $processes = $external_fulfillment->getJobProcesses();

    $cash_admin->page_data['job_name'] = $external_fulfillment->job_name;
    $cash_admin->page_data['processes'] = $processes;
    $cash_admin->page_data['processes_count'] = count($processes);
    $cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',$item_response['payload']['fulfillment_asset'],$cash_admin->getAllFavoriteAssets(),true);

    // show process page with release asset selection
    $cash_admin->setPageContentTemplate('commerce_externalfulfillment_process');
}

if ($action == "show_detail" || $action == "detail") {
    // show an existing job and edit

    if (!empty($request_parameters[1])) {
        $fulfillment_job_id = $request_parameters[1];
        $fulfillment_job = $external_fulfillment->getUserJobById($fulfillment_job_id);
        $cash_admin->page_data['job'] = $fulfillment_job[0];//print_r($fulfillment_job, true);
        $cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',$fulfillment_job[0]['asset_id'],$cash_admin->getAllFavoriteAssets(),true);

        $cash_admin->page_data['order_count'] = $external_fulfillment->getOrderCountByJob($fulfillment_job_id);
        $cash_admin->page_data['completed_orders'] =
            $external_fulfillment->getOrderCountByJob($fulfillment_job_id,
                    [
                        'name' => 'complete',
                        'value' => true
                    ]
                );

        $cash_admin->page_data['imcomplete_orders'] =
            $external_fulfillment->getOrderCountByJob($fulfillment_job_id,
                [
                    'name' => 'complete',
                    'value' => false
                ]
            );

        $cash_admin->setPageContentTemplate('commerce_externalfulfillment_detail');

    } else {
        // error
    }


}

if ($action == "send") {
    $cash_admin->page_data['ui_title'] = 'Send mass email to backers';
    $cash_admin->page_data['id'] = $request_parameters[1];

    $cash_admin->setPageContentTemplate('commerce_externalfulfillment_send');
}
?>

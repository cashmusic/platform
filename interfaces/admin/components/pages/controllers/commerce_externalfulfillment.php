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

error_log("####!!! Action: $action");

/**
 * Behind the scenes controller actions
 */

// create a fulfillment seed with the effective user id
$user_id = AdminHelper::getPersistentData('cash_effective_user');
$external_fulfillment = new ExternalFulfillmentSeed($user_id);

if ($action == "do_create") {
    // create the fulfillment job

    error_log(
        "do_create"
    );
    
    $external_fulfillment->createOrContinueJob();

    // set the view to show upload dialog
    $action = "show_upload";
}

if ($action == "do_upload") {
    // process uploads one by one; we're not setting a template here
    // because we're going to have it redirect on completion only

    error_log(
      "do_upload"
    );

    if (!empty($_FILES['csv_upload'])) {

        $external_fulfillment
            ->createOrContinueJob("created")    // only grab it if it has status 'created'
            ->parseUpload($_FILES['csv_upload'])
            ->createJobProcesses();

    } else {
        // there's an issue, we're do_uploading without an upload
    }

}

if ($action == "do_process") {
    // we're renaming each tier, and actually doing the tier to order conversion here
    // attaching a release asset for fulfillment

    error_log(
        "do_process"
    );

    $external_fulfillment
        ->createOrContinueJob("process")    // only grab the job if it's status 'process'
        ->createTiers()
        ->updateFulfillmentJobStatus("processed");

    // set the view to the job detail, because we're done
    $action = "show_detail";

}


/**
 * View switch
 */
if ($action == "show_index") {
    // just show the current job or option to create a new one
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

if ($action == "show_process" || $action == "process") {

    // this step we need to load the job manually here, because of the way the view is called

    $external_fulfillment
        ->createOrContinueJob("created")
        ->updateFulfillmentJobStatus("process");

    // load pending processes for this job and list them

    // set whatever values we need for the template
    $cash_admin->page_data['job_name'] = $external_fulfillment->job_name;
    // show process page with release asset selection
    $cash_admin->setPageContentTemplate('commerce_externalfulfillment_process');
}

if ($action == "show_detail") {
    // show an existing job; also the final display
    //$cash_admin->setPageContentTemplate('commerce_externalfulfillment_detail');
}

?>
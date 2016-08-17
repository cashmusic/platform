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

if ($action == "do_process" || $action == "process") {
    // we're renaming each tier, and actually doing the tier to order conversion here
    // attaching a release asset for fulfillment

    error_log(
        "do_process"
    );

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

// if we've got this key then we need to override--- not really a better way to retain the URI and do this
if ($action == "detail" && !empty($_REQUEST['fulfillment_job_id'])) $action = "do_change";

if ($action == "do_change") {
    // we're renaming each tier, and actually doing the tier to order conversion here
    // attaching a release asset for fulfillment
    error_log(
        "do_change"
    );

    if (!empty($_REQUEST['fulfillment_job_id'])) {


        $id = $_REQUEST['fulfillment_job_id'];

        $update = [];

        if (!empty($_REQUEST['item_fulfillment_asset'])) {
            $update = array_merge($update, ['asset_id' => $_REQUEST['item_fulfillment_asset']]);
        }

        $external_fulfillment
            ->updateFulfillmentJob($update, $id)
            ->updateTiers();
    }


    // set the view to the job detail, because we're done
    $action = "show_detail";
}


/**
 * View switch
 */
if ($action == "show_index") {
    // show existing jobs, and a create new job button

    $cash_admin->page_data['user_jobs'] = $external_fulfillment->getUserJobs();
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
        $fulfillment_job = $external_fulfillment->getUserJobById($request_parameters[1]);
        $cash_admin->page_data['job'] = $fulfillment_job[0];//print_r($fulfillment_job, true);
        $cash_admin->page_data['asset_options'] = AdminHelper::echoFormOptions('assets',$fulfillment_job[0]['asset_id'],$cash_admin->getAllFavoriteAssets(),true);

        $cash_admin->setPageContentTemplate('commerce_externalfulfillment_detail');

    } else {
        // error
    }


}

?>
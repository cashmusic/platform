<?php

// process uploads and shit
if (!empty($_FILES)) {

    $total_added = 0;
    $uploaded_emails = file_get_contents($_FILES['element_upload']['tmp_name']);

    $email_array = CASHSystem::parseBulkEmailInput($uploaded_emails);

    if (count($email_array) > 50000) {
        $email_array_chunks = array_chunk($email_array, 50000);
        foreach($email_array_chunks as $email_chunk) {
            // skip invalid emails
            //TODO: benchmark this because good glob
            $email_chunk = filter_var_array($email_chunk,FILTER_VALIDATE_EMAIL);

            $created_response = $cash_admin->requestAndStore(
                array(
                    'cash_request_type' => 'people',
                    'cash_action' => 'addbulkaddresses',
                    'do_not_verify' => 1,
                    'addresses' => $email_chunk,
                    'list_id' => $request_parameters[0]
                )
            );

            if ($created_user_ids = $created_response['payload']) {
                $list_response = $cash_admin->requestAndStore(
                    array(
                        'cash_request_type' => 'people',
                        'cash_action' => 'addbulklistmembers',
                        'user_ids' => $created_user_ids,
                        'list_id' => $request_parameters[0]
                    )
                );

                $total_added += count($created_user_ids);
            }
        }
    } else {
        // 50000 or under
        $email_array = filter_var_array($email_array,FILTER_VALIDATE_EMAIL);

        $created_response = $cash_admin->requestAndStore(
            array(
                'cash_request_type' => 'people',
                'cash_action' => 'addbulkaddresses',
                'do_not_verify' => 1,
                'addresses' => $email_array,
                'list_id' => $request_parameters[0]
            )
        );

        if ($created_user_ids = $created_response['payload']) {
            $list_response = $cash_admin->requestAndStore(
                array(
                    'cash_request_type' => 'people',
                    'cash_action' => 'addbulklistmembers',
                    'user_ids' => $created_user_ids,
                    'list_id' => $request_parameters[0]
                )
            );

            $total_added = count($created_user_ids);
        }
    }

    if (count($email_array) > 0) {
        echo '{"success":"true"}';
    } else {
        echo '{"success":"false"}';
    }
} else {
    echo '{"success":"false"}';
}

exit();

?>
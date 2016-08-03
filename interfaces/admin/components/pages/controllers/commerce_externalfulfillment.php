<?php
$cash_admin->page_data['foo'] = print_r($_REQUEST, true);

if (isset($_REQUEST['docsvimport'])) {
    $cash_admin->page_data['foo'] = "hey"; //print_r($_FILES, true);
}

$cash_admin->setPageContentTemplate('commerce_externalfulfillment');
?>
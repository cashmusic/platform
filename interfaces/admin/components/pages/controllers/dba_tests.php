<?php

namespace CASHMusic\Admin;

use CASHMusic\Entities\User;

$user = new User();
$user->username = "this shouxxxld workzzz";
$user->email_address = "my@email.orgz";
$user->password = "is a password";
$user->first_name = "Tommy";
$user->last_name = "Filepp";

$user->save();

echo $user->password;
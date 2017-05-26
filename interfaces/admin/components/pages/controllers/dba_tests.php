<?php

namespace CASHMusic\Admin;

use CASHMusic\Entities\User;

$user = new User();
$user->username = "foo";
$user->email_address = "my@email.orgz";
$user->password = "is a password";
$user->first_name = "Tommy";
$user->last_name = "Filepp";
$user->data = ['foo','bar'=> ['foo', 'bar']];
$user->save();

echo $user->password;
echo print_r($user->data, true);
<?php

namespace CASHMusic\Admin;

use CASHMusic\Core\CASHDBAL;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Entities\User;

$db = CASHDBAL::entityManager();

//CASHSystem::dd($db);

$user = new User();
$user->username = "dasadssad dasdasdas bro";
$user->email_address = "idasddassaddasnfo@bodasdsasdasobs.org";
$user->password = "poopzzzz";

$user->save();

echo $user->password;
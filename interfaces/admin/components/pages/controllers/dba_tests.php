<?php

namespace CASHMusic\Admin;

use CASHMusic\Entities\People;

$user = People::create([
    'username'=>"booby",
    'email_address'=>"boobies@boobies.com",
    'password'=>"floobies",
    'data'=>['123','1345']
]);

echo print_r($user, true);
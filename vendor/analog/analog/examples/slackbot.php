<?php

require '../lib/Analog.php';

Analog::handler (Analog\Handler\Slackbot::init ('teamname', 'token', 'channel'));

Analog::log ('Error message', Analog::WARNING);

?>
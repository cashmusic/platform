<?php

namespace CASHMusic\Tools;

require_once(dirname(__FILE__) . '/vendor/autoload.php');

use CASHMusic\Core\CASHSystem;
use CASHMusic\Core\CASHDBAL;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

CASHSystem::startUp();

return ConsoleRunner::createHelperSet(CASHDBAL::entityManager());
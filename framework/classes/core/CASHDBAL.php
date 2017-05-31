<?php

namespace CASHMusic\Core;

use CASHMusic\Core\CASHSystem;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

class CASHDBAL {

    public static function entityManager()
    {
        $paths = array(CASH_PLATFORM_ROOT."/classes/entities");
        $isDevMode = false;

        $cash_db_settings = CASHSystem::getSystemSettings();

        $dbParams = array(
            'driver'   => 'pdo_mysql',
            'user'     => $cash_db_settings['username'],
            'password' => $cash_db_settings['password'],
            'dbname'   => $cash_db_settings['database'],
        );

        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        return EntityManager::create($dbParams, $config);

    }


}
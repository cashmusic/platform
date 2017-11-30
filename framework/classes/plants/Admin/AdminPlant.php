<?php

namespace CASHMusic\Plants\Admin;

use CASHMusic\Core\CASHDBAL;
use CASHMusic\Core\PlantBase;
use CASHMusic\Core\CASHRequest;
use CASHMusic\Core\CASHSystem;
use CASHMusic\Admin\AdminHelper;
use CASHMusic\Entities\Asset;
use CASHMusic\Entities\AssetAnalytic;
use CASHMusic\Entities\AssetAnalyticsBasic;
use CASHMusic\Entities\People;
use CASHMusic\Entities\SystemMetadata;
use CASHMusic\Seeds\S3Seed;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Pixie\Exception;
use CASHMusic\Entities\SystemSettings;

class AdminPlant extends PlantBase
{
    public function __construct($request_type, $request)
    {
        $this->request_type = 'admin';
        $this->getRoutingTable();

        $this->plantPrep($request_type, $request);
    }

    public function getSchema($table) {
        $user = $this->orm->findWhere(SystemSettings::class, [] );
        return $user;
    }
}
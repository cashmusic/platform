<?php
namespace CASHMusic\Core;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;


class CASHEntity
{
    public $pdo, $em;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
        $this->em = self::entityManager($this->pdo);
    }

    public static function entityManager($pdo)
    {
        $paths = array(CASH_PLATFORM_ROOT."/classes/entities");
        $isDevMode = true;

        //$cash_db_settings = CASHSystem::getSystemSettings();

        $dbParams = array(
            'driver'   => 'pdo_mysql',
            'pdo' => $pdo
        );

        try {

            $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
            $config->addEntityNamespace("CASHMusic", "CASHMusic\\Entities\\");
        } catch (\Exception $e) {
            CASHSystem::errorLog($e->getMessage());
            return false;
        }

        return EntityManager::create($dbParams, $config);
    }

    public function find($entity, $id) {
        return $entity::find($this->em, $id);
    }

    public function findWhere($entity, $values, $force_array=false, $order_by=null, $limit=null, $offset=null) {
        return $entity::findWhere($this->em, $values, $force_array, $order_by, $limit, $offset);
    }

    public function all($entity, $limit=null, $order_by=null, $offset=null) {
        return $entity::all($this->em, $limit, $order_by, $offset);
    }

    public function create($entity, $values) {
        return $entity::create($this->em, $values);
    }

    public function delete($entity, $values) {

        try {
            $object = $entity::findWhere($this->em, $values, false);

            if (is_array($object)) {
                foreach ($object as $o) {
                    $o->delete();
                }
            } else {
                $object->delete();
            }
        } catch (\Exception $e) {
            CASHSystem::errorLog($e->getMessage());
            return false;
        }

        return true;
    }

}
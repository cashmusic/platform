<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHSystem;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class CASHEntityRepository extends EntityRepository
{


    public function search(array $required_values, array $search)
    {
        CASHSystem::errorLog($required_values);
        CASHSystem::errorLog($search);
        /*$expr = Criteria::expr();
        $criteria = Criteria::create();

        $reqs = [];
        foreach($required_values as $field=>$value) {
           $reqs[] = $expr->eq($field, $value);
        }

        $searches = [];
        foreach($search as $field=>$value) {

            $searches[] = $expr->contains($field, $value);
        }

        $criteria->where(call_user_func_array(array( $criteria->expr(), 'andX' ),$reqs))
                ->where(call_user_func_array(array( $criteria->expr(), 'orX' ),$searches));
        return $this->matching($criteria);*/

        return $this->getEntityManager()
            ->createQueryBuilder('p')
            ->setParameter('status', '%active%')
            ->getQuery()
            ->getResult();
    }


}
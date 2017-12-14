<?php

namespace CASHMusic\Entities;

use CASHMusic\Core\CASHSystem;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class CASHEntityRepository extends EntityRepository
{


    public function search(array $required_values, array $search)
    {
        $criteria = Criteria::create();

        $reqs = [];
        foreach($required_values as $field=>$value) {
           $reqs[] = Criteria::expr()->eq($field, $value);
        }

        $searches = [];
        foreach($search as $field=>$value) {

            $searches[] = Criteria::expr()->contains($field, $value);
        }

        $criteria->where(call_user_func_array(array( $criteria->expr(), 'andX' ),$reqs))
                ->andWhere(call_user_func_array(array( $criteria->expr(), 'orX' ),$searches));
        return $this->matching($criteria);



        return $test;
    }


}
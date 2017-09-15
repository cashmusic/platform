<?php

namespace CASHMusic\Entities;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class CASHEntityRepository extends EntityRepository
{


    public function search($search)
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where($expr->contains('email', $search));
        return $this->matching($criteria);
    }


}
<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function findByIds(array $ids)
    {
        if (empty($ids)) {
            return null;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from('WoojinGoodsBundle:Category', 'c')
            ->where(
                $qb->expr()->in('c.id', $ids)
            )
        ;

        return $qb->getQuery()->getResult();
    }
}




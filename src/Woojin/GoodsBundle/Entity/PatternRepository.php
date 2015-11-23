<?php
// src/Acme/StoreBundle/Entity/ProductRepository.php
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PatternRepository extends EntityRepository
{
    public function findValid()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select(array('p', 'y'))
            ->from('WoojinGoodsBundle:Pattern', 'p')
            ->leftJoin('p.yc', 'y')
            ->where($qb->expr()->gt('p.count', 0))
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}




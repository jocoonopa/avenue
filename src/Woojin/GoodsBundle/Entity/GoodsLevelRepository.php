<?php
// src/Acme/StoreBundle/Entity/ProductRepository.php
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class GoodsLevelRepository extends EntityRepository
{
    public function findValid()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p')
            ->from('WoojinGoodsBundle:GoodsLevel', 'p')
            ->where($qb->expr()->gt('p.count', 0))
            ->getQuery()
            ->getResult()
        ;
    }
}




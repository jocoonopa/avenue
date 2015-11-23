<?php
// src/Acme/StoreBundle/Entity/ProductRepository.php
namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class StoreRepository extends EntityRepository
{
    public function findReal()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('s')
            ->from('WoojinStoreBundle:Store', 's')
            ->where($qb->expr()->lte('s.id', 5))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAll()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('s')
            ->from('WoojinStoreBundle:Store', 's')
            ->where($qb->expr()->neq('s.id', 7))
            ->getQuery()
            ->getResult()
        ;
    }
}




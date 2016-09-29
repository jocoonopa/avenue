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

    public function findIsShow()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('s')
            ->from('WoojinStoreBundle:Store', 's')
            ->where($qb->expr()->eq('s.isShow', true))
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

    public function findBsoOptions()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('s')
            ->from('WoojinStoreBundle:Store', 's')
            ->where($qb->expr()->in('s.sn', array('Y', 'Z', 'X', 'P', '!')))
            ->getQuery()
            ->getResult()
        ;
    }

    public function genStoreSnMap()
    {
        $map = array();
        $stores = $this->findAll();

        foreach ($stores as $store) {
            $map[$store->getSn()] = $store;
        }

        return $map;
    }
}

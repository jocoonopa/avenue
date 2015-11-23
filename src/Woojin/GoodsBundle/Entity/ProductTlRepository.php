<?php
// src/Acme/StoreBundle/Entity/ProductRepository.php
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProductTlRepository extends EntityRepository
{
    public function findNotExpired()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $date = new \DateTime();

        return $qb->select(array('p', 'g'))
            ->from('WoojinGoodsBundle:ProductTl', 'p')
            ->leftJoin('p.product', 'g')
            ->where($qb->expr()->gte('p.endAt', $qb->expr()->literal($date->format('Y-m-d H:i:s'))))
            ->getQuery()
            ->getResult()
        ;
    }
}




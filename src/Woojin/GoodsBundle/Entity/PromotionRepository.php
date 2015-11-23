<?php
// src/Acme/StoreBundle/Entity/ProductRepository.php
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PromotionRepository extends EntityRepository
{
    public function findValid()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $date = new \DateTime();

        return $qb->select(array('p', 'g', 'i'))
            ->from('WoojinGoodsBundle:Promotion', 'p')
            ->leftJoin('p.goodsPassports', 'g')
            ->leftJoin('g.img', 'i')
            ->where( 
                $qb->expr()->andX( 
                    $qb->expr()->lte('p.startAt', $qb->expr()->literal($date->format('Y-m-d H:i:s'))), 
                    $qb->expr()->gte('p.stopAt', $qb->expr()->literal($date->format('Y-m-d H:i:s'))),
                    //$qb->expr()->gt('p.count', 0),
                    $qb->expr()->eq('p.isDisplay', true)
                )  
            )
            ->orderBy('p.sort', 'DESC')
            ->addOrderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}




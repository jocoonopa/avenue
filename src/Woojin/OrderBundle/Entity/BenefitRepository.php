<?php
// src/Acme/StoreBundle/Entity/ProductRepository.php
namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Woojin\OrderBundle\Entity\Custom;

class BenefitRepository extends EntityRepository
{
    public function findOwn(Custom $custom)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('p')
            ->from('WoojinOrderBundle:Benefit', 'p')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->lte('p.expireAt', $qb->expr()->literal($date->format('Y-m-d H:i:s'))),
                    $qb->expr()->gt('p.remain', 0),
                    $qb->expr()->eq('p.custom', $custom->getId())
                )
            )
            ->orderBy('p.expireAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNowUsingEvent()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $date = new \DateTime();

        return $qb->select('p')
            ->from('WoojinOrderBundle:BenefitEvent', 'p')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gte('p.startAt', $qb->expr()->literal($date->format('Y-m-d H:i:s'))),
                    $qb->expr()->lte('p.endAt', $qb->expr()->literal($date->format('Y-m-d H:i:s')))
                )
            )
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}




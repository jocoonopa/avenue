<?php

namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Woojin\StoreBundle\Entity\Store;
use Woojin\UserBundle\Entity\User;

/**
 * CustomRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomRepository extends EntityRepository
{
    const STORE_WEBSITE = 8;

    /**
     * 電子信箱 + 密碼驗證客戶，如符合則回傳該客戶實體，不符合回傳 null
     *
     * @param  string $email
     * @param  string $password
     * @return \Woojin\OrderBundle\Entity\Custom | false
     */
    public function findByEmailAndPasswordCheck($email, $password)
    {
        $custom = $this->findByEmailFromWebsite($email);

        return ($custom && $custom->verifyLogin($password, $email)) ? $custom : null;
    }

    /**
     * 透過電子信箱查找有無符合的官網客戶，若有則回傳該客戶實體，不符合則回傳 null
     *
     * @param  string $email
     * @return \Woojin\OrderBundle\Entity\Custom | false
     */
    public function findByEmailFromWebsite($email)
    {
        $em = $this->getEntityManager();

        return $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(
            array(
                'email' => $email,
                'store' => self::STORE_WEBSITE
            )
        );
    }

    /**
     * 透過FBUserNode 找尋客戶
     *
     * @param  object $userNode
     * @return \Woojin\OrderBundle\Entity\Custom | false
     */
    public function findByFaceBookUserNode($userNode)
    {
        $em = $this->getEntityManager();
        $custom = NULL;

        if (isset($userNode['id'])) {
            $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(
                array(
                    'fbToken' => $userNode['id'],
                    'store' => self::STORE_WEBSITE
                )
            );
        }

        if (NULL === $custom && isset($userNode['email'])) {
            $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(
                array(
                    'email' => $userNode['email'],
                    'store' => self::STORE_WEBSITE
                )
            );
        }

        return $custom;
    }

    /**
     * 透過GoogleId 找尋客戶
     *
     * @param  array $node
     * @return \Woojin\OrderBundle\Entity\Custom | false
     */
    public function findByGoogleNode(array $node)
    {
        $em = $this->getEntityManager();
        $custom = NULL;

        if (isset($node['sub'])) {
            $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(
                array(
                    'googleToken' => $node['sub'],
                    'store' => self::STORE_WEBSITE
                )
            );
        }

        if (NULL === $custom && isset($node['email'])) {
            $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy(
                array(
                    'email' => $node['email'],
                    'store' => self::STORE_WEBSITE
                )
            );
        }

        return $custom;
    }

    /**
     * 找尋本店使用該手機號碼的客戶
     *
     * @param  Woojin\UserBundle\Entity\User   $user  Custom own store's user
     * @param  string $mobil
     * @return \Woojin\OrderBundle\Entity\Custom | null
     */
    public function findOwnUseTheMobil(User $user, $mobil)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.mobil', $qb->expr()->literal($mobil)),
                    $qb->expr()->neq('c.mobil', $qb->expr()->literal('')),
                    $qb->expr()->eq('c.store', $user->getStore()->getId())
                )
            )
        ;

        $res = $qb->getQuery()->getOneOrNullResult();

        return $res;
    }

    /**
     * For the purpose of typeahead
     *
     * @param  Woojin\UserBundle\Entity\User   $user
     * @param  string $mobil
     * @return array
     */
    public function getTypeahead(User $user, $mobil)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->like('c.mobil', '?1'),
                    $qb->expr()->eq('c.store', $user->getStore()->getId())
                )
            )
        ;

        $qb->setParameter('1', "{$mobil}%");

        return $qb->getQuery()->getResult();
    }

    /**
     * 找尋本店使用該手機號碼的客戶
     *
     * @param  Woojin\StoreBundle\Entity\Store   $store  Custom own store
     * @param  string $mobil
     * @return \Woojin\OrderBundle\Entity\Custom | null
     */
    public function findByMobilAndStore(Store $store, $mobil)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.mobil', $qb->expr()->literal($mobil)),
                    $qb->expr()->neq('c.mobil', $qb->expr()->literal('')),
                    $qb->expr()->eq('c.store', $store->getId())
                )
            )
        ;

        return $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();
    }

    /**
     * 找尋本店使用該手機號碼的客戶
     *
     * @param  Woojin\StoreBundle\Entity\Store   $store  Custom own store
     * @param  string $mobil
     * @return \Woojin\OrderBundle\Entity\Custom | null
     */
    public function findMultiByMobilAndStore(Store $store, $mobil)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.mobil', $qb->expr()->literal($mobil)),
                    $qb->expr()->neq('c.mobil', $qb->expr()->literal('')),
                    $qb->expr()->eq('c.store', $store->getId())
                )
            )
        ;

        $res = $qb->getQuery()->getResult();

        return $res;
    }

    public function findByNameAndStore(Store $store, $name)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.name', $qb->expr()->literal($name)),
                    $qb->expr()->neq('c.name', $qb->expr()->literal('')),
                    $qb->expr()->eq('c.mobil', $qb->expr()->literal('')),
                    $qb->expr()->eq('c.store', $store->getId())
                )
            )
        ;

        $res = $qb->getQuery()->getOneOrNullResult();

        return $res;
    }
}

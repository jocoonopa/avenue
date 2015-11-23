<?php

// src/Acme/UserBundle/Entity/UserRepository.php
namespace Woojin\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

use Woojin\Utility\Avenue\Avenue;

class UserRepository extends EntityRepository implements UserProviderInterface
{
    public function loadUserByUsername($username)
    {
        $q = $this->createQueryBuilder('u')
            ->select('u, r')
            ->leftJoin('u.roles', 'r')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery();

        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active admin WoojinUserBundle:User object identified by "%s".',
                $username
            );
            
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);

        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $class));
        }

        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }

    /**
     * 取得某一商品之網路訂單負責人
     *
     * @param string $storeSn 產編第一碼(店碼)
     * @return  \Woojin\UserBundle\Entity\User
     */
    public function findWebOrderChargePerson($sn)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('u')
            ->from('WoojinUserBundle:User', 'u')
            ->leftJoin('u.store', 's')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('u.roles', Avenue::ROLE_CHIEF), // 權限為店長
                    $qb->expr()->eq('u.isActive', Avenue::USER_IS_ACTIVE),  // 確認帳號啟用
                    $qb->expr()->eq('s.sn', $qb->expr()->literan($sn)) // 確認商品為該店商品
                )
            )
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}











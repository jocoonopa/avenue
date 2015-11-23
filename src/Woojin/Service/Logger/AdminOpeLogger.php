<?php

namespace Woojin\Service\Logger;

use Woojin\OrderBundle\Entity\Orders;
use Woojin\OrderBundle\Entity\Ope;
use Woojin\OrderBundle\Entity\PayType;
use Woojin\UserBundle\Entity\User;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class AdminOpeLogger
{
    protected $ope;
	protected $context;
	protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em, SecurityContext $context)
    {
        $this->context = $context;
        $this->em = $em;
    }

    public function getUser()
    {
        return $this->context->getToken()->getUser();
    }

	public function recordOpe($order, $act, $user = null, $time = null)
	{
		$user = ($user == null) ? $this->getUser() : $user;
		
		$ope = new Ope();
		$ope
			->setOrders($order)
			->setAct($act)
			->setUser($user)
			->setDatetime((is_null($time) || !is_object($time)) ? new \DateTime(date('Y-m-d H:i:s')) : $time)
		;

		$this->em->persist($ope);
		$this->em->flush();

		return $ope;
	}

    public function log(Orders $order, User $user, PayType $paytype, $amount = null, $act = null)
    {
        $ope = new Ope;

        $ope
            ->setOrder($order)
            ->setUser($user)
            ->setPayType($paytype)
            ->setAmount($amount)
            ->setAct($act)
            ->setDatetime(new \DateTime())
        ;

        $this->setOpe($ope);

        return $this;
    }

    public function setOpe(Ope $ope)
    {
        $this->ope = $ope;

        return $this;
    }

    public function getOpe()
    {
        return $this->ope;
    }
}



<?php

namespace Woojin\StoreBundle;

use Woojin\StoreBundle\Entity\MetaRecord;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class MetaRecordMethod
{
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

	public function recordMeta($act)
	{
		$user = $this->getUser();

        if (!is_object($user)) {
            throw new \Exception('Session timeout!');
        }

		$oMetaRecord = new MetaRecord();
		$oMetaRecord
            ->setAct($act)
			->setUser($user)
			->setDatetime(new \DateTime(date('Y-m-d H:i:s')))
        ;

		$this->em->persist($oMetaRecord);
		$this->em->flush();

		return new Response('ok');
	}
}
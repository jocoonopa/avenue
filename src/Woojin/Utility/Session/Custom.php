<?php

namespace Woojin\Utility\Session;

class Custom
{
    private $session;

    private $em;

    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session, \Doctrine\ORM\EntityManager $em)
    {
        $this->session = $session;

        $this->em = $em;
    }

    public function current($isProhibit = false, $isActive = false)
    {
        $customStructure = json_decode($this->session->get('custom'));

        if (empty($customStructure)) {
            return null;
        }

        /**
         * Custom Entity
         * 
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $this->em->find('WoojinOrderBundle:Custom', $customStructure->id);

        if ($isProhibit && $custom->getIsProhibit()) {
            return null;
        }

        if ($isActive && !$custom->isActive()) {
            return null;
        }

        return $custom;
    }
}
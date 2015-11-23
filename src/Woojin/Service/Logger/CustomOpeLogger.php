<?php

namespace Woojin\Service\Logger;

use Woojin\OrderBundle\Entity\Custom;
use Woojin\OrderBundle\Entity\CustomOpe;

class CustomOpeLogger
{
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function write(Custom $custom, array $content)
    {
        $customOpe = new CustomOpe($custom, json_encode($content));
        $this->em->persist($custom);
        $this->em->flush();

        return $this;
    }
}
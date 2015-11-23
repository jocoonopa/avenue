<?php

namespace Woojin\AgencyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AgencyController extends Controller
{
  /**
   * @Route("/agency")
   * @Template()
   */
  public function indexAction ()
  {
    $em = $this->getDoctrine()->getManager();
    $entities = $em->getRepository('WoojinAgencyBundle:AgencyItem')->findAll();
    return array(
      'entities' => $entities,
    );
  }

  // public function objectAddmentAction ()
  // {

  // }
}

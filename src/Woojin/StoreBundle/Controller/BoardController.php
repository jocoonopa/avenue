<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Board controller.
 *
 * @Route("/board")
 */
class BoardController extends Controller
{
  /**
   * @Route("/", name="store_board")
   * @Template()
   * @Method("GET")
   */
  public function indexAction()
  {
    return array();
  }

  /**
   * @Route("/email", name="store_email")
   * @Method("GET")
   */
  public function emailAction()
  {
  	$message = \Swift_Message::newInstance('smtp.gmail.com', 465, 'ssl')
      ->setSubject('Hello Email')
      ->setFrom('jocoonopa@gmail.com')
      ->setTo('jocoonopa@gmail.com')
      ->setBody('hello there?')
    ;

    try {
    	$this->get('mailer')->send($message);
    } catch (Exception $e) {
    	throw $e;
    }

    return new Response('??');
  }
}
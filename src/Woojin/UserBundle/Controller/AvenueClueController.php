<?php

namespace Woojin\UserBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\UserBundle\Entity\AvenueClue;

/**
 * AvenueClue controller.
 *
 * @Route("/avenueclue")
 */
class AvenueClueController extends Controller
{

    /**
     * Lists all AvenueClue entities.
     *
     * @Route("/", name="avenueclue")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinUserBundle:AvenueClue')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a AvenueClue entity.
     *
     * @Route("/{id}", name="avenueclue_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinUserBundle:AvenueClue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AvenueClue entity.');
        }

        return array(
            'entity'      => $entity,
        );
    }
}

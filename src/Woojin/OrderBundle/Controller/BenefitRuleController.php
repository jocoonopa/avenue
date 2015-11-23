<?php

namespace Woojin\OrderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Woojin\OrderBundle\Entity\BenefitRule;

/**
 * BenefitRule controller.
 *
 * @Route("/benefitrule")
 */
class BenefitRuleController extends Controller
{
    /**
     * Lists all BenefitRule entities.
     *
     * @Route("/", name="benefitrule")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $rules = $em->getRepository('WoojinOrderBundle:BenefitRule')->findAll();

        return array(
            'rules' => $rules,
        );
    }

    /**
     * @Route("", name="admin_benefitRule_new")
     * @Method("POST")
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $rule = new BenefitRule;
        $rule
            ->setEvent($em->find('WoojinOrderBundle:BenefitEvent', $request->request->get('event_id')))
            ->setSill($request->request->get('sill'))
            ->setCeiling($request->request->get('ceiling'))
            ->setGift($request->request->get('gift'))
            ->setIsStack($request->request->get('is_stack') == 1)
        ;

        $em->persist($rule);
        $em->flush();

        $session = $this->get('session');

        // set flash messages
        $session->getFlashBag()->add('success', '新增規則完成!');

        return $this->redirect($this->generateUrl('benefitevent_edit', 
            array(
                'id' => $request->request->get('event_id'),
                'isRule' => true
            )
        ));
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="admin_benefitRule_update")
     * @ParamConverter("rule", class="WoojinOrderBundle:BenefitRule")
     * @Method("PUT")
     */
    public function updateAction(Request $request, BenefitRule $rule)
    {
        $em = $this->getDoctrine()->getManager();

        $eventId = $rule->getEvent()->getId();
        
        $rule
            ->setSill($request->request->get('sill'))
            ->setCeiling($request->request->get('ceiling'))
            ->setGift($request->request->get('gift'))
            ->setIsStack($request->request->get('is_stack') == 1)
        ;
        $em->persist($rule);
        $em->flush();

        $session = $this->get('session');

        // set flash messages
        $session->getFlashBag()->add('success', '修改規則完成!');

        return $this->redirect($this->generateUrl('benefitevent_edit', 
            array(
                'id' => $eventId,
                'isRule' => true
            )
        ));
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="admin_benefitRule_delete")
     * @ParamConverter("rule", class="WoojinOrderBundle:BenefitRule")
     * @Method("DELETE")
     */
    public function destroyAction(Request $request, BenefitRule $rule)
    {
        $eventId = $rule->getEvent()->getId();

        $em = $this->getDoctrine()->getManager();
        $em->remove($rule);
        $em->flush();

        $session = $this->get('session');

        // set flash messages
        $session->getFlashBag()->add('success', '刪除規則完成!');

        return $this->redirect($this->generateUrl('benefitevent_edit', 
            array(
                'id' => $eventId,
                'isRule' => true
            )
        ));
    }
}

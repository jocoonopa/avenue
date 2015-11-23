<?php

namespace Woojin\OrderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\OrderBundle\Entity\BenefitEvent;
use Woojin\OrderBundle\Form\BenefitEventType;

/**
 * BenefitEvent controller.
 *
 * @Route("/benefitevent")
 */
class BenefitEventController extends Controller
{

    /**
     * Lists all BenefitEvent entities.
     *
     * @Route("/", name="benefitevent")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinOrderBundle:BenefitEvent')->findAll();

        return array(
            'events' => $entities,
        );
    }
    /**
     * Creates a new BenefitEvent entity.
     *
     * @Route("/", name="benefitevent_create")
     * @Method("POST")
     * @Template("WoojinOrderBundle:BenefitEvent:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new BenefitEvent();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('benefitevent_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a BenefitEvent entity.
     *
     * @param BenefitEvent $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(BenefitEvent $entity)
    {
        $form = $this->createForm(new BenefitEventType(), $entity, array(
            'action' => $this->generateUrl('benefitevent_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new BenefitEvent entity.
     *
     * @Route("/new", name="benefitevent_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new BenefitEvent();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a BenefitEvent entity.
     *
     * @Route("/{id}", name="benefitevent_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinOrderBundle:BenefitEvent')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BenefitEvent entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing BenefitEvent entity.
     *
     * @Route("/{id}/edit/{isRule}", defaults={"isRule"="0"}, name="benefitevent_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id, $isRule)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinOrderBundle:BenefitEvent')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BenefitEvent entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'event'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'isRule' => ($isRule == 1)
        );
    }

    /**
    * Creates a form to edit a BenefitEvent entity.
    *
    * @param BenefitEvent $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(BenefitEvent $entity)
    {
        $form = $this->createForm(new BenefitEventType(), $entity, array(
            'action' => $this->generateUrl('benefitevent_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing BenefitEvent entity.
     *
     * @Route("/{id}", name="benefitevent_update")
     * @Method("PUT")
     * @Template("WoojinOrderBundle:BenefitEvent:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinOrderBundle:BenefitEvent')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BenefitEvent entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $session = $this->get('session');

            // set flash messages
            $session->getFlashBag()->add('success', '修改購物金活動完成!');

            return $this->redirect($this->generateUrl('benefitevent_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a BenefitEvent entity.
     *
     * @Route("/{id}", name="benefitevent_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinOrderBundle:BenefitEvent')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find BenefitEvent entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('benefitevent'));
    }

    /**
     * Creates a form to delete a BenefitEvent entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('benefitevent_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

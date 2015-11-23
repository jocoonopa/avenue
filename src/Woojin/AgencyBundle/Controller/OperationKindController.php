<?php

namespace Woojin\AgencyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\AgencyBundle\Entity\OperationKind;
use Woojin\AgencyBundle\Form\OperationKindType;

/**
 * OperationKind controller.
 *
 * @Route("/operationkind")
 */
class OperationKindController extends Controller
{

    /**
     * Lists all OperationKind entities.
     *
     * @Route("/", name="operationkind")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinAgencyBundle:OperationKind')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new OperationKind entity.
     *
     * @Route("/", name="operationkind_create")
     * @Method("POST")
     * @Template("WoojinAgencyBundle:OperationKind:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new OperationKind();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('operationkind_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a OperationKind entity.
    *
    * @param OperationKind $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(OperationKind $entity)
    {
        $form = $this->createForm(new OperationKindType(), $entity, array(
            'action' => $this->generateUrl('operationkind_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OperationKind entity.
     *
     * @Route("/new", name="operationkind_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new OperationKind();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a OperationKind entity.
     *
     * @Route("/{id}", name="operationkind_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:OperationKind')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperationKind entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing OperationKind entity.
     *
     * @Route("/{id}/edit", name="operationkind_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:OperationKind')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperationKind entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a OperationKind entity.
    *
    * @param OperationKind $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OperationKind $entity)
    {
        $form = $this->createForm(new OperationKindType(), $entity, array(
            'action' => $this->generateUrl('operationkind_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OperationKind entity.
     *
     * @Route("/{id}", name="operationkind_update")
     * @Method("PUT")
     * @Template("WoojinAgencyBundle:OperationKind:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:OperationKind')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperationKind entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('operationkind_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a OperationKind entity.
     *
     * @Route("/{id}", name="operationkind_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinAgencyBundle:OperationKind')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OperationKind entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('operationkind'));
    }

    /**
     * Creates a form to delete a OperationKind entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('operationkind_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

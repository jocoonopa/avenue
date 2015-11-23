<?php

namespace Woojin\AgencyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\AgencyBundle\Entity\OperationRecord;
use Woojin\AgencyBundle\Form\OperationRecordType;

/**
 * OperationRecord controller.
 *
 * @Route("/operationrecord")
 */
class OperationRecordController extends Controller
{

    /**
     * Lists all OperationRecord entities.
     *
     * @Route("/", name="operationrecord")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinAgencyBundle:OperationRecord')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new OperationRecord entity.
     *
     * @Route("/", name="operationrecord_create")
     * @Method("POST")
     * @Template("WoojinAgencyBundle:OperationRecord:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new OperationRecord();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('operationrecord_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a OperationRecord entity.
    *
    * @param OperationRecord $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(OperationRecord $entity)
    {
        $form = $this->createForm(new OperationRecordType(), $entity, array(
            'action' => $this->generateUrl('operationrecord_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new OperationRecord entity.
     *
     * @Route("/new", name="operationrecord_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new OperationRecord();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a OperationRecord entity.
     *
     * @Route("/{id}", name="operationrecord_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:OperationRecord')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperationRecord entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing OperationRecord entity.
     *
     * @Route("/{id}/edit", name="operationrecord_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:OperationRecord')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperationRecord entity.');
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
    * Creates a form to edit a OperationRecord entity.
    *
    * @param OperationRecord $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(OperationRecord $entity)
    {
        $form = $this->createForm(new OperationRecordType(), $entity, array(
            'action' => $this->generateUrl('operationrecord_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing OperationRecord entity.
     *
     * @Route("/{id}", name="operationrecord_update")
     * @Method("PUT")
     * @Template("WoojinAgencyBundle:OperationRecord:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:OperationRecord')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find OperationRecord entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('operationrecord_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a OperationRecord entity.
     *
     * @Route("/{id}", name="operationrecord_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinAgencyBundle:OperationRecord')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find OperationRecord entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('operationrecord'));
    }

    /**
     * Creates a form to delete a OperationRecord entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('operationrecord_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

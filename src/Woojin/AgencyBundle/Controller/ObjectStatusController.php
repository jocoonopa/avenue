<?php

namespace Woojin\AgencyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\AgencyBundle\Entity\ObjectStatus;
use Woojin\AgencyBundle\Form\ObjectStatusType;

/**
 * ObjectStatus controller.
 *
 * @Route("/objectstatus")
 */
class ObjectStatusController extends Controller
{

    /**
     * Lists all ObjectStatus entities.
     *
     * @Route("/", name="objectstatus")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinAgencyBundle:ObjectStatus')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new ObjectStatus entity.
     *
     * @Route("/", name="objectstatus_create")
     * @Method("POST")
     * @Template("WoojinAgencyBundle:ObjectStatus:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ObjectStatus();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('objectstatus_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a ObjectStatus entity.
    *
    * @param ObjectStatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(ObjectStatus $entity)
    {
        $form = $this->createForm(new ObjectStatusType(), $entity, array(
            'action' => $this->generateUrl('objectstatus_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ObjectStatus entity.
     *
     * @Route("/new", name="objectstatus_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ObjectStatus();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a ObjectStatus entity.
     *
     * @Route("/{id}", name="objectstatus_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:ObjectStatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObjectStatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ObjectStatus entity.
     *
     * @Route("/{id}/edit", name="objectstatus_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:ObjectStatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObjectStatus entity.');
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
    * Creates a form to edit a ObjectStatus entity.
    *
    * @param ObjectStatus $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ObjectStatus $entity)
    {
        $form = $this->createForm(new ObjectStatusType(), $entity, array(
            'action' => $this->generateUrl('objectstatus_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ObjectStatus entity.
     *
     * @Route("/{id}", name="objectstatus_update")
     * @Method("PUT")
     * @Template("WoojinAgencyBundle:ObjectStatus:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:ObjectStatus')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObjectStatus entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('objectstatus_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a ObjectStatus entity.
     *
     * @Route("/{id}", name="objectstatus_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinAgencyBundle:ObjectStatus')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ObjectStatus entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('objectstatus'));
    }

    /**
     * Creates a form to delete a ObjectStatus entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('objectstatus_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

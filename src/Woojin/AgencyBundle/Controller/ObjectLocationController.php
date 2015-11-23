<?php

namespace Woojin\AgencyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\AgencyBundle\Entity\ObjectLocation;
use Woojin\AgencyBundle\Form\ObjectLocationType;

/**
 * ObjectLocation controller.
 *
 * @Route("/objectlocation")
 */
class ObjectLocationController extends Controller
{

    /**
     * Lists all ObjectLocation entities.
     *
     * @Route("/", name="objectlocation")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinAgencyBundle:ObjectLocation')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new ObjectLocation entity.
     *
     * @Route("/", name="objectlocation_create")
     * @Method("POST")
     * @Template("WoojinAgencyBundle:ObjectLocation:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ObjectLocation();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('objectlocation_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a ObjectLocation entity.
    *
    * @param ObjectLocation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(ObjectLocation $entity)
    {
        $form = $this->createForm(new ObjectLocationType(), $entity, array(
            'action' => $this->generateUrl('objectlocation_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ObjectLocation entity.
     *
     * @Route("/new", name="objectlocation_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ObjectLocation();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a ObjectLocation entity.
     *
     * @Route("/{id}", name="objectlocation_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:ObjectLocation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObjectLocation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ObjectLocation entity.
     *
     * @Route("/{id}/edit", name="objectlocation_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:ObjectLocation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObjectLocation entity.');
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
    * Creates a form to edit a ObjectLocation entity.
    *
    * @param ObjectLocation $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ObjectLocation $entity)
    {
        $form = $this->createForm(new ObjectLocationType(), $entity, array(
            'action' => $this->generateUrl('objectlocation_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ObjectLocation entity.
     *
     * @Route("/{id}", name="objectlocation_update")
     * @Method("PUT")
     * @Template("WoojinAgencyBundle:ObjectLocation:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:ObjectLocation')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObjectLocation entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('objectlocation_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a ObjectLocation entity.
     *
     * @Route("/{id}", name="objectlocation_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinAgencyBundle:ObjectLocation')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ObjectLocation entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('objectlocation'));
    }

    /**
     * Creates a form to delete a ObjectLocation entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('objectlocation_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

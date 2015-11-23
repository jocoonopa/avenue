<?php

namespace Woojin\AgencyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\AgencyBundle\Entity\AgencyItem;
use Woojin\AgencyBundle\Form\AgencyItemType;

/**
 * AgencyItem controller.
 *
 * @Route("/agencyitem")
 */
class AgencyItemController extends Controller
{

    /**
     * Lists all AgencyItem entities.
     *
     * @Route("/", name="agencyitem")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinAgencyBundle:AgencyItem')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new AgencyItem entity.
     *
     * @Route("/", name="agencyitem_create")
     * @Method("POST")
     * @Template("WoojinAgencyBundle:AgencyItem:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new AgencyItem();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('agencyitem_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a AgencyItem entity.
    *
    * @param AgencyItem $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(AgencyItem $entity)
    {
        $form = $this->createForm(new AgencyItemType(), $entity, array(
            'action' => $this->generateUrl('agencyitem_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new AgencyItem entity.
     *
     * @Route("/new", name="agencyitem_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new AgencyItem();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a AgencyItem entity.
     *
     * @Route("/{id}", name="agencyitem_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:AgencyItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AgencyItem entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing AgencyItem entity.
     *
     * @Route("/{id}/edit", name="agencyitem_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:AgencyItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AgencyItem entity.');
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
    * Creates a form to edit a AgencyItem entity.
    *
    * @param AgencyItem $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(AgencyItem $entity)
    {
        $form = $this->createForm(new AgencyItemType(), $entity, array(
            'action' => $this->generateUrl('agencyitem_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing AgencyItem entity.
     *
     * @Route("/{id}", name="agencyitem_update")
     * @Method("PUT")
     * @Template("WoojinAgencyBundle:AgencyItem:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:AgencyItem')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find AgencyItem entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('agencyitem_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a AgencyItem entity.
     *
     * @Route("/{id}", name="agencyitem_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinAgencyBundle:AgencyItem')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find AgencyItem entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('agencyitem'));
    }

    /**
     * Creates a form to delete a AgencyItem entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('agencyitem_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

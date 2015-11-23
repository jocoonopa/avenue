<?php

namespace Woojin\AgencyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\AgencyBundle\Entity\Contractor;
use Woojin\AgencyBundle\Form\ContractorType;

/**
 * Contractor controller.
 *
 * @Route("/contractor")
 */
class ContractorController extends Controller
{

    /**
     * Lists all Contractor entities.
     *
     * @Route("/", name="contractor")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinAgencyBundle:Contractor')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Contractor entity.
     *
     * @Route("/", name="contractor_create")
     * @Method("POST")
     * @Template("WoojinAgencyBundle:Contractor:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Contractor();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('contractor_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Contractor entity.
    *
    * @param Contractor $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Contractor $entity)
    {
        $form = $this->createForm(new ContractorType(), $entity, array(
            'action' => $this->generateUrl('contractor_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Contractor entity.
     *
     * @Route("/new", name="contractor_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Contractor();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Contractor entity.
     *
     * @Route("/{id}", name="contractor_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:Contractor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contractor entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Contractor entity.
     *
     * @Route("/{id}/edit", name="contractor_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:Contractor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contractor entity.');
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
    * Creates a form to edit a Contractor entity.
    *
    * @param Contractor $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Contractor $entity)
    {
        $form = $this->createForm(new ContractorType(), $entity, array(
            'action' => $this->generateUrl('contractor_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Contractor entity.
     *
     * @Route("/{id}", name="contractor_update")
     * @Method("PUT")
     * @Template("WoojinAgencyBundle:Contractor:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinAgencyBundle:Contractor')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Contractor entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('contractor_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Contractor entity.
     *
     * @Route("/{id}", name="contractor_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinAgencyBundle:Contractor')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Contractor entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('contractor'));
    }

    /**
     * Creates a form to delete a Contractor entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('contractor_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

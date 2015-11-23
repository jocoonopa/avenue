<?php

namespace Woojin\OrderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\OrderBundle\Entity\PayType;
use Woojin\OrderBundle\Form\PayTypeType;

/**
 * PayType controller.
 *
 * @Route("/paytype")
 */
class PayTypeController extends Controller
{

    /**
     * Lists all PayType entities.
     *
     * @Route("/", name="paytype")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinOrderBundle:PayType')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new PayType entity.
     *
     * @Route("/", name="paytype_create")
     * @Method("POST")
     * @Template("WoojinOrderBundle:PayType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new PayType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('paytype_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a PayType entity.
     *
     * @param PayType $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(PayType $entity)
    {
        $form = $this->createForm(new PayTypeType(), $entity, array(
            'action' => $this->generateUrl('paytype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new PayType entity.
     *
     * @Route("/new", name="paytype_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new PayType();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a PayType entity.
     *
     * @Route("/{id}", name="paytype_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinOrderBundle:PayType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PayType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing PayType entity.
     *
     * @Route("/{id}/edit", name="paytype_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinOrderBundle:PayType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PayType entity.');
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
    * Creates a form to edit a PayType entity.
    *
    * @param PayType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(PayType $entity)
    {
        $form = $this->createForm(new PayTypeType(), $entity, array(
            'action' => $this->generateUrl('paytype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing PayType entity.
     *
     * @Route("/{id}", name="paytype_update")
     * @Method("PUT")
     * @Template("WoojinOrderBundle:PayType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinOrderBundle:PayType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PayType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('paytype_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a PayType entity.
     *
     * @Route("/{id}", name="paytype_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinOrderBundle:PayType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find PayType entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('paytype'));
    }

    /**
     * Creates a form to delete a PayType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('paytype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

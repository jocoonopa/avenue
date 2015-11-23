<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\GoodsBundle\Entity\Pattern;
use Woojin\GoodsBundle\Form\PatternType;

/**
 * Pattern controller.
 *
 * @Route("/pattern")
 */
class PatternController extends Controller
{
    /**
     * Lists all Pattern entities.
     *
     * @Route("/", name="pattern")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinGoodsBundle:Pattern')->findBy(array(), array('name' => 'DESC'));

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Pattern entity.
     *
     * @Route("/", name="pattern_create")
     * @Method("POST")
     * @Template("WoojinGoodsBundle:Pattern:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Pattern();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('pattern_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Pattern entity.
     *
     * @param Pattern $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Pattern $entity)
    {
        $form = $this->createForm(new PatternType(), $entity, array(
            'action' => $this->generateUrl('pattern_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Pattern entity.
     *
     * @Route("/new", name="pattern_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Pattern();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Pattern entity.
     *
     * @Route("/{id}", requirements={"page" = "\d+"}, name="pattern_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Pattern')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pattern entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Pattern entity.
     *
     * @Route("/{id}/edit", requirements={"page" = "\d+"}, name="pattern_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Pattern')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pattern entity.');
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
    * Creates a form to edit a Pattern entity.
    *
    * @param Pattern $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Pattern $entity)
    {
        $form = $this->createForm(new PatternType(), $entity, array(
            'action' => $this->generateUrl('pattern_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => '更新'));

        return $form;
    }
    /**
     * Edits an existing Pattern entity.
     *
     * @Route("/{id}", requirements={"page" = "\d+"}, name="pattern_update")
     * @Method("PUT")
     * @Template("WoojinGoodsBundle:Pattern:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Pattern')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Pattern entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('pattern_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Pattern entity.
     *
     * @Route("/{id}", requirements={"page" = "\d+"}, name="pattern_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinGoodsBundle:Pattern')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Pattern entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('pattern'));
    }

    /**
     * Creates a form to delete a Pattern entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('pattern_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => '刪除'))
            ->getForm()
        ;
    }
}

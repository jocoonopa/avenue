<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\GoodsBundle\Entity\SeoSlogan;
use Woojin\GoodsBundle\Form\SeoSloganType;

/**
 * SeoSlogan controller.
 *
 * @Route("/seoslogan")
 */
class SeoSloganController extends Controller
{

    /**
     * Lists all SeoSlogan entities.
     *
     * @Route("/", name="seoslogan")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinGoodsBundle:SeoSlogan')->findBy(array(), array('name' => 'DESC'));

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new SeoSlogan entity.
     *
     * @Route("/", name="seoslogan_create")
     * @Method("POST")
     * @Template("WoojinGoodsBundle:SeoSlogan:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new SeoSlogan();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $session = $this->get('session');
            $session->getFlashBag()->add('success', '成功新增關鍵字: ' . $entity->getName());

            return $this->redirect($this->generateUrl('seoslogan'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a SeoSlogan entity.
     *
     * @param SeoSlogan $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(SeoSlogan $entity)
    {
        $form = $this->createForm(new SeoSloganType(), $entity, array(
            'action' => $this->generateUrl('seoslogan_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new SeoSlogan entity.
     *
     * @Route("/new", name="seoslogan_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new SeoSlogan();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a SeoSlogan entity.
     *
     * @Route("/{id}", name="seoslogan_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:SeoSlogan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SeoSlogan entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing SeoSlogan entity.
     *
     * @Route("/{id}/edit", name="seoslogan_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:SeoSlogan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SeoSlogan entity.');
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
    * Creates a form to edit a SeoSlogan entity.
    *
    * @param SeoSlogan $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(SeoSlogan $entity)
    {
        $form = $this->createForm(new SeoSloganType(), $entity, array(
            'action' => $this->generateUrl('seoslogan_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing SeoSlogan entity.
     *
     * @Route("/{id}", name="seoslogan_update")
     * @Method("PUT")
     * @Template("WoojinGoodsBundle:SeoSlogan:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:SeoSlogan')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SeoSlogan entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $session = $this->get('session');
            $session->getFlashBag()->add('success', '成功編輯關鍵字: ' . $entity->getName());

            return $this->redirect($this->generateUrl('seoslogan'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a SeoSlogan entity.
     *
     * @Route("/{id}", name="seoslogan_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinGoodsBundle:SeoSlogan')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SeoSlogan entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('seoslogan'));
    }

    /**
     * Creates a form to delete a SeoSlogan entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('seoslogan_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

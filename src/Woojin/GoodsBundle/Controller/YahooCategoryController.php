<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\GoodsBundle\Entity\YahooCategory;
use Woojin\GoodsBundle\Form\YahooCategoryType;

/**
 * YahooCategory controller.
 *
 * @Route("/yahoocategory")
 */
class YahooCategoryController extends Controller
{

    /**
     * Lists all YahooCategory entities.
     *
     * @Route("/", name="yahoocategory")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinGoodsBundle:YahooCategory')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new YahooCategory entity.
     *
     * @Route("/", name="yahoocategory_create")
     * @Method("POST")
     * @Template("WoojinGoodsBundle:YahooCategory:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new YahooCategory();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('yahoocategory_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a YahooCategory entity.
     *
     * @param YahooCategory $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(YahooCategory $entity)
    {
        $form = $this->createForm(new YahooCategoryType(), $entity, array(
            'action' => $this->generateUrl('yahoocategory_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new YahooCategory entity.
     *
     * @Route("/new", name="yahoocategory_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new YahooCategory();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a YahooCategory entity.
     *
     * @Route("/{id}", name="yahoocategory_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:YahooCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find YahooCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing YahooCategory entity.
     *
     * @Route("/{id}/edit", name="yahoocategory_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:YahooCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find YahooCategory entity.');
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
    * Creates a form to edit a YahooCategory entity.
    *
    * @param YahooCategory $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(YahooCategory $entity)
    {
        $form = $this->createForm(new YahooCategoryType(), $entity, array(
            'action' => $this->generateUrl('yahoocategory_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing YahooCategory entity.
     *
     * @Route("/{id}", name="yahoocategory_update")
     * @Method("PUT")
     * @Template("WoojinGoodsBundle:YahooCategory:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:YahooCategory')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find YahooCategory entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $session = $this->get('session');

            // set flash messages
            $session->getFlashBag()->add('success', $entity->getName() . '修改完成!');

            return $this->redirect($this->generateUrl('yahoocategory'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a YahooCategory entity.
     *
     * @Route("/{id}", name="yahoocategory_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinGoodsBundle:YahooCategory')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find YahooCategory entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('yahoocategory'));
    }

    /**
     * Creates a form to delete a YahooCategory entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('yahoocategory_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Woojin\StoreBundle\Entity\Store;
use Woojin\StoreBundle\Form\StoreType;
use Woojin\OrderBundle\Entity\Orders;
use Woojin\StoreBundle\StoreEvents;
use Woojin\StoreBundle\Event\PurchaseEvent;
use Woojin\StoreBundle\Event\StoreSubscriber;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

/**
 * Store controller.
 *
 * @Route("/store")
 */
class StoreController extends Controller
{
    /**
     * Lists all Store entities.
     *
     * @Route("/", name="admin_store")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinStoreBundle:Store')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Store entity.
     *
     * @Route("/", name="admin_store_create")
     * @Method("POST")
     * @Template("WoojinStoreBundle:Store:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Store();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_store_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Store entity.
     *
     * @param Store $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Store $entity)
    {
        $form = $this->createForm(new StoreType(), $entity, array(
            'action' => $this->generateUrl('admin_store_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Store entity.
     *
     * @Route("/new", name="admin_store_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Store();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Store entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="admin_store_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinStoreBundle:Store')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Store entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Store entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="admin_store_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if ($user->getStore()->getId() != $id) {
            throw new \Exception('您不是該店同仁不可設定相關資訊!');
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinStoreBundle:Store')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Store entity.');
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
    * Creates a form to edit a Store entity.
    *
    * @param Store $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Store $entity)
    {
        $form = $this->createForm(new StoreType(), $entity, array(
            'action' => $this->generateUrl('admin_store_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => '確定', 'attr' => array(
            'class' => 'btn btn-primary'
        )));

        return $form;
    }
    /**
     * Edits an existing Store entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="admin_store_update")
     * @Method("PUT")
     * @Template("WoojinStoreBundle:Store:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinStoreBundle:Store')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Store entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_store_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Store entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="admin_store_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinStoreBundle:Store')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Store entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_store'));
    }

    /**
     * @Route("/test", name="admin_store_test")
     * @Method("GET")
     */
    public function eventTestAction()
    {
        $auctionService = $this->get('auction.service');
        $auctionService->create(array());

        return new Response('');
    }

    /**
     * Creates a form to delete a Store entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_store_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

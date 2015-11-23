<?php

namespace Woojin\AgencyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\AgencyBundle\Entity\ObjectImage;
use Woojin\AgencyBundle\Form\ObjectImageType;

/**
 * ObjectImage controller.
 *
 * @Route("/objectimage")
 */
class ObjectImageController extends Controller
{

  /**
   * Lists all ObjectImage entities.
   *
   * @Route("/", name="objectimage")
   * @Method("GET")
   * @Template()
   */
  public function indexAction()
  {
    $em = $this->getDoctrine()->getManager();

    $entities = $em->getRepository('WoojinAgencyBundle:ObjectImage')->findAll();

    return array(
      'entities' => $entities,
    );
  }
  /**
   * Creates a new ObjectImage entity.
   *
   * @Route("/", name="objectimage_create")
   * @Method("POST")
   * @Template("WoojinAgencyBundle:ObjectImage:new.html.twig")
   */
  public function createAction(Request $request)
  {
    $entity = new ObjectImage();
    $form = $this->createCreateForm($entity);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity->upload();
      $em->persist($entity);
      $em->flush();

      return $this->redirect($this->generateUrl('objectimage_show', array('id' => $entity->getId())));
    }

    return array(
      'entity' => $entity,
      'form'   => $form->createView(),
    );
  }

  /**
  * Creates a form to create a ObjectImage entity.
  *
  * @param ObjectImage $entity The entity
  *
  * @return \Symfony\Component\Form\Form The form
  */
  private function createCreateForm(ObjectImage $entity)
  {
    $form = $this->createForm(new ObjectImageType(), $entity, array(
      'action' => $this->generateUrl('objectimage_create'),
      'method' => 'POST',
    ));

    $form->add('submit', 'submit', array('label' => 'Create'));

    return $form;
  }

  /**
   * Displays a form to create a new ObjectImage entity.
   *
   * @Route("/new", name="objectimage_new")
   * @Method("GET")
   * @Template()
   */
  public function newAction()
  {
    $entity = new ObjectImage();
    $form   = $this->createCreateForm($entity);

    return array(
      'entity' => $entity,
      'form'   => $form->createView(),
    );
  }

  /**
   * Finds and displays a ObjectImage entity.
   *
   * @Route("/{id}", name="objectimage_show")
   * @Method("GET")
   * @Template()
   */
  public function showAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('WoojinAgencyBundle:ObjectImage')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find ObjectImage entity.');
    }

    $deleteForm = $this->createDeleteForm($id);

    return array(
      'entity'      => $entity,
      'delete_form' => $deleteForm->createView(),
    );
  }

  /**
   * Displays a form to edit an existing ObjectImage entity.
   *
   * @Route("/{id}/edit", name="objectimage_edit")
   * @Method("GET")
   * @Template()
   */
  public function editAction($id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('WoojinAgencyBundle:ObjectImage')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find ObjectImage entity.');
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
  * Creates a form to edit a ObjectImage entity.
  *
  * @param ObjectImage $entity The entity
  *
  * @return \Symfony\Component\Form\Form The form
  */
  private function createEditForm(ObjectImage $entity)
  {
    $form = $this->createForm(new ObjectImageType(), $entity, array(
      'action' => $this->generateUrl('objectimage_update', array('id' => $entity->getId())),
      'method' => 'PUT',
    ));

    $form->add('submit', 'submit', array('label' => 'Update'));

    return $form;
  }
  /**
   * Edits an existing ObjectImage entity.
   *
   * @Route("/{id}", name="objectimage_update")
   * @Method("PUT")
   * @Template("WoojinAgencyBundle:ObjectImage:edit.html.twig")
   */
  public function updateAction(Request $request, $id)
  {
    $em = $this->getDoctrine()->getManager();

    $entity = $em->getRepository('WoojinAgencyBundle:ObjectImage')->find($id);

    if (!$entity) {
      throw $this->createNotFoundException('Unable to find ObjectImage entity.');
    }

    $deleteForm = $this->createDeleteForm($id);
    $editForm = $this->createEditForm($entity);
    $editForm->handleRequest($request);

    if ($editForm->isValid()) {
      $entity->upload();
      $em->flush();

      return $this->redirect($this->generateUrl('objectimage_edit', array('id' => $id)));
    }

    return array(
      'entity'      => $entity,
      'edit_form'   => $editForm->createView(),
      'delete_form' => $deleteForm->createView(),
    );
  }
  /**
   * Deletes a ObjectImage entity.
   *
   * @Route("/{id}", name="objectimage_delete")
   * @Method("DELETE")
   */
  public function deleteAction(Request $request, $id)
  {
    $form = $this->createDeleteForm($id);
    $form->handleRequest($request);

    if ($form->isValid()) {
      $em = $this->getDoctrine()->getManager();
      $entity = $em->getRepository('WoojinAgencyBundle:ObjectImage')->find($id);

      if (!$entity) {
        throw $this->createNotFoundException('Unable to find ObjectImage entity.');
      }

      $em->remove($entity);
      $em->flush();
    }

    return $this->redirect($this->generateUrl('objectimage'));
  }

  /**
   * Creates a form to delete a ObjectImage entity by id.
   *
   * @param mixed $id The entity id
   *
   * @return \Symfony\Component\Form\Form The form
   */
  private function createDeleteForm($id)
  {
    return $this->createFormBuilder()
      ->setAction($this->generateUrl('objectimage_delete', array('id' => $id)))
      ->setMethod('DELETE')
      ->add('submit', 'submit', array('label' => 'Delete'))
      ->getForm()
    ;
  }
}

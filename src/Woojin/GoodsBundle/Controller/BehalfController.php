<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\GoodsBundle\Entity\Behalf;
use Woojin\GoodsBundle\Form\BehalfType;
use Woojin\Utility\Avenue\Avenue;
/**
 * Behalf controller.
 *
 * @Route("/behalf")
 */
class BehalfController extends Controller
{
    /**
     * Lists all Behalf entities.
     *
     * @Route("/", name="behalf")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinGoodsBundle:Behalf')->findBy(array(), array('updateAt' => 'DESC'));

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Behalf entity.
     *
     * @Route("/", name="behalf_create")
     * @Method("POST")
     * @Template("WoojinGoodsBundle:Behalf:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $session = $this->get('session');

        if (!$session->get('custom')) {
            throw new \Exception('請先登入會員');
        }

        $form = $this->get('form.factory')->createBuilder('form')->getForm();
        $form->bind($request);
        if (!$form->isValid()) {
            throw new \Exception('憑證過期囉請重新登入!');
        }

        $behalfFac = $this->get('factory.behalf');
        $behalfFac->create(
            $this->get('session.custom')->current(true, true),
            $this->get('facade.product')->find($request->request->get('product_id'))
        );

        return $this->generateUrl('front_profile_behalf');
    }

    /**
     * Creates a form to create a Behalf entity.
     *
     * @param Behalf $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Behalf $entity)
    {
        $form = $this->createForm(new BehalfType(), $entity, array(
            'action' => $this->generateUrl('behalf_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Behalf entity.
     *
     * @Route("/new", name="behalf_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Behalf();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Behalf entity.
     *
     * @Route("/{id}", name="behalf_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Behalf')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Behalf entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Behalf entity.
     *
     * @Route("/{id}/edit", name="behalf_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $behalf = $em->getRepository('WoojinGoodsBundle:Behalf')->find($id);

        return array('behalf' => $behalf);
    }

    /**
    * Creates a form to edit a Behalf entity.
    *
    * @param Behalf $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Behalf $entity)
    {
        $form = $this->createForm(new BehalfType(), $entity, array(
            'action' => $this->generateUrl('behalf_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Behalf entity.
     *
     * @Route("/{id}", name="behalf_update")
     * @Method("PUT")
     * @Template("WoojinGoodsBundle:Behalf:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $behalf = $em->getRepository('WoojinGoodsBundle:Behalf')->findOneBy(
            array('id' => $id, 'status' => $request->request->get('status'))
        );

        if (!$behalf) {
            throw $this->createNotFoundException('Unable to find Behalf entity.');
        }

        if (!$this->get('form.csrf_provider')->isCsrfTokenValid('admin_behalf', $request->request->get('_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        if ((int)$request->request->get('to_status') !== (int) ($behalf->getStatus()->getId() + 1)) {
            throw new AccessDeniedHttpException('Wrong assignment');
        }
        
        $this->get('factory.behalf')->nextStep(
            array(
                'behalf' => $behalf,
                'required' => $request->request->get('required'),
                'deliverySn' => $request->request->get('delivery_sn')
        ));
        $em->flush();

        return $this->redirect($this->generateUrl('behalf_edit', array('id' => $id)));
    }
    /**
     * Deletes a Behalf entity.
     *
     * @Route("/{id}", name="behalf_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $behalf = $em->getRepository('WoojinGoodsBundle:Behalf')->find(array('id' => $id));

        if (in_array($behalf->getStatus()->getId(), array(
            Avenue::BS_PURIN,
            Avenue::BS_PUROUT, 
            Avenue::BS_CANCEL, 
            Avenue::BS_AVENUE_CANCEL,
            Avenue::BS_CHARGE_BACK
        ))) {
            throw new AccessDeniedHttpException('此代購狀態不可取消');
        }

        if ($behalf->getStatus()->getId() === Avenue::BS_NOT_CONFIRM) {
            $behalf->setStatus($this->get('facade.behalf.status')->findCancel());
        } else {
            $behalf->setStatus($this->get('facade.behalf.status')->findAvenueCancel());
        }

        $em->persist($behalf);
        $em->flush();

        return $this->redirect($this->generateUrl('behalf'));
    }

    /**
     * Creates a form to delete a Behalf entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('behalf_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}

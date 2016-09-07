<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Woojin\GoodsBundle\Entity\Promotion;
use Woojin\GoodsBundle\Form\PromotionType;
use Woojin\Utility\Handler\ResponseHandler;
use Woojin\Utility\Avenue\Avenue;

/**
 * Promotion controller.
 *
 * @Route("/promotion")
 */
class PromotionController extends Controller
{
    /**
     * Lists all Promotion entities.
     *
     * @Route("/", name="promotion")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinGoodsBundle:Promotion')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Promotion entity.
     *
     * @Route("/", name="promotion_create")
     * @Method("POST")
     * @Template("WoojinGoodsBundle:Promotion:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Promotion();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity->setUser($user);

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('promotion_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * 促銷活動管理關連商品介面
     * 
     * @Route("/{id}/relate", name="promotion_relate", options={"expose"=true})
     * @ParamConverter("promotion", class="WoojinGoodsBundle:Promotion")
     * @Method("GET")
     * @Template()
     */
    public function relateAction(Promotion $promotion)
    {
        $em = $this->getDoctrine()->getManager();

        $brands = $em->getRepository('WoojinGoodsBundle:Brand')->findAll();

        $patterns = $em->getRepository('WoojinGoodsBundle:Pattern')->findAll();

        $colors = $em->getRepository('WoojinGoodsBundle:Color')->findAll();

        $promotions = $em->getRepository('WoojinGoodsBundle:Promotion')->findAll();

        return array(
            'promotions' => $promotions,
            'promotion' => $promotion,
            'brands' => $brands,
            'patterns' => $patterns,
            'colors' => $colors
        );
    }

    /**
     * Creates a form to create a Promotion entity.
     *
     * @param Promotion $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Promotion $entity)
    {
        $form = $this->createForm(new PromotionType(), $entity, array(
            'action' => $this->generateUrl('promotion_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array(
            'label' => '新增'
        ));

        return $form;
    }

    /**
     * Displays a form to create a new Promotion entity.
     *
     * @Route("/new", name="promotion_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Promotion();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Promotion entity.
     *
     * @Route("/{id}", name="promotion_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Promotion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Promotion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Promotion entity.
     *
     * @Route("/{id}/edit", name="promotion_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Promotion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Promotion entity.');
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
    * Creates a form to edit a Promotion entity.
    *
    * @param Promotion $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Promotion $entity)
    {
        $form = $this->createForm(new PromotionType(), $entity, array(
            'action' => $this->generateUrl('promotion_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array(
            'label' => '更新',             
            'attr' => array(
                'class' => 'btn btn-success pull-left'
        )));

        return $form;
    }
    /**
     * Edits an existing Promotion entity.
     *
     * @Route("/{id}", name="promotion_update")
     * @Method("PUT")
     * @Template("WoojinGoodsBundle:Promotion:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Promotion')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Promotion entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            // if ($entity->hasSold()) {
            //     $session = $this->get('avenue.session')->get(); 
            //     $session->getFlashBag()->add('error', '存在已售出商品，不可任意修改活動資訊');
                
            //     return $this->redirect($this->generateUrl('promotion_edit', array('id' => $id)));
            // }

            $em->flush();

            return $this->redirect($this->generateUrl('promotion_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Promotion entity.
     *
     * @Route("/{id}", name="promotion_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinGoodsBundle:Promotion')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Promotion entity.');
            }

            // 判斷有無存在已經售出的商品，若有則不可刪除本活動
            if ($entity->hasSold()) {
                $session = $this->get('avenue.session')->get(); 
                $session->getFlashBag()->add('error', '已經存在售出商品， 不可刪除');

                return $this->redirect($this->generateUrl('promotion_edit', array('id' => $id)));
            }

            $iterator = $entity->getIterator();
            while ($iterator->valid()) {
                $product = $iterator->current();
                $product->setPromotion(null);
                $em->persist($product);
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('promotion'));
    }



    /**
     * Creates a form to delete a Promotion entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('promotion_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => '刪除', 'attr' => array(
                'class' => 'btn btn-danger pull-right'
            )))
            ->getForm()
        ;
    }

    /**
     * 多筆查詢店內商品的 api，目前用在促銷活動上
     * 
     * @Route("/api/fetch/{_format}", defaults={"_format"="json"}, name="promotion_api_fetch", options={"expose"=true})
     * @Method("POST")
     */
    public function fetchByConAction(Request $request, $_format)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        
        $em = $this->getDoctrine()->getManager();

        $products = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->getMultile($request, $user->getStore());

        $jProducts = $serializer->serialize($products, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getResponse($jProducts, $_format);
    }

    /**
     * 取得該促銷活動已經售出之商品
     * 
     * @Route("/api/{id}/sold/{_format}", defaults={"_format"="json"}, name="promotion_api_fetch_sold", options={"expose"=true})
     * @ParamConverter("promotion", class="WoojinGoodsBundle:Promotion")
     * @Method("GET")
     */
    public function fetchSoldoutAction(Promotion $promotion, $_format)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();

        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.orders', 'o')
            ->where(
                $qb->expr()->eq('g.promotion', $promotion->getId()),
                $qb->expr()->eq('g.status', Avenue::GS_SOLDOUT),
                $qb->expr()->eq('o.kind', Avenue::OK_OFFICIAL)
                // $qb->expr()->eq(
                //     $qb->expr()->substring('g.sn', 1, 1),
                //     $qb->expr()->literal($user->getStore()->getSn())
                // )
            )
        ;

        $products = $qb->getQuery()->getResult();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jProducts = $serializer->serialize($products, $_format);

        $responseHandler = new ResponseHandler;
       
        return $responseHandler->getResponse($jProducts, $_format); 
    }

    /**
     * 將傳入的商品id作為條件選取出商品陣列，並將陣列內的商品從本促銷活動中移除
     * 
     * @Route("/api/{id}/product", name="promotion_api_product_delete", options={"expose"=true})
     * @ParamConverter("promotion", class="WoojinGoodsBundle:Promotion")
     * @Method("PUT")
     */
    public function removeAction(Request $request, Promotion $promotion)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                // $qb->expr()->eq(
                //     $qb->expr()->substring('g.sn', 1, 1), 
                //     $qb->expr()->literal($user->getStore()->getSn())
                // ),
                $qb->expr()->in('g.id', json_decode($request->request->get('ids'))),
                $qb->expr()->eq('g.promotion', $promotion->getId())
            )
        ;

        $products = $qb->getQuery()->getResult();

        foreach ($products as $product) {
            $product->setPromotion(null);
            $em->persist($product);
        }

        $em->flush();

        return new Response('ok');
    }

    /**
     * 已傳入的商品id選取出商品陣列，並將陣列內的商品加入本促銷活動
     * 
     * @Route("/api/{id}/product", name="promotion_api_product_add", options={"expose"=true})
     * @ParamConverter("promotion", class="WoojinGoodsBundle:Promotion")
     * @Method("POST")
     */
    public function addAction(Request $request, Promotion $promotion)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                // $qb->expr()->eq(
                //     $qb->expr()->substring('g.sn', 1, 1), 
                //     $qb->expr()->literal($user->getStore()->getSn())
                // ),
                $qb->expr()->in('g.id', json_decode($request->request->get('ids')))
                //$qb->expr()->neq('g.promotion', $promotion->getId())
            )
        ;

        $products = $qb->getQuery()->getResult();

        foreach ($products as $product) {
            $product->setPromotion($promotion);
            $em->persist($product);
        }

        $em->flush();

        return new Response('ok');
    }
}

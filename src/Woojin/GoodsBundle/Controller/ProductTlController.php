<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Woojin\GoodsBundle\Entity\ProductTl;
use Woojin\GoodsBundle\Form\ProductTlType;

/**
 * ProductTl controller.
 *
 * @Route("/producttl")
 */
class ProductTlController extends Controller
{

    /**
     * Lists all ProductTl entities.
     *
     * @Route("/", name="producttl")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $tls = $em->getRepository('WoojinGoodsBundle:ProductTl')->findAll();

        $entities = $em->getRepository('WoojinGoodsBundle:ProductTl')->findAll();

        return array(
            'entities' => $entities,
            'tls' => $tls
        );
    }
    /**
     * Creates a new ProductTl entity.
     *
     * @Route("/", name="producttl_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $sn = $request->request->get('sn');

        $product = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findOneBy(array('sn' => $sn, 'isAllowWeb' => true));

        if (!$product) {
            $request->getSession()->getFlashBag()->add(
                'error',
                '找不到商品' . $sn
            );

            return $this->redirect($this->generateUrl('producttl'));
        }

        $tl = new ProductTl;
        $tl
            ->setPrice($request->request->get('price'))
            ->setEndAt(new \DateTime($request->request->get('end_at')))
        ;

        $em->persist($tl);

        $product->setProductTl($tl);
        $em->persist($product);
        $em->flush();

        $request->getSession()->getFlashBag()->add(
                'success',
                $sn . '搶購新增成功'
            );

        return $this->redirect($this->generateUrl('producttl')); 
    }

    /**
     * Edits an existing ProductTl entity.
     *
     * @Route("/{id}", name="producttl_update", options={"expose"=true})
     * @ParamConverter("tl", class="WoojinGoodsBundle:ProductTl")
     * @Method("PUT")
     */
    public function updateAction(Request $request, ProductTl $tl)
    {
        $em = $this->getDoctrine()->getManager();

        $tl
            ->setPrice($request->request->get('price', $tl->getPrice()))
            ->setEndAt(new \DateTime($request->request->get('end_at')))
        ;
        $em->persist($tl);
        $em->flush();

        return new Response(json_encode(array('status' => 1, 'msg' => 'ok')));
    }
    /**
     * Deletes a ProductTl entity.
     *
     * @Route("/{id}", name="producttl_delete")
     * @ParamConverter("tl", class="WoojinGoodsBundle:ProductTl")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ProductTl $tl)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($tl);

        $product = $tl->getProduct();

        if ($product) {
            $product->setProductTl(null);

            $em->persist($product);
        }
        
        $em->flush();

        $request->getSession()->getFlashBag()->add(
                'success',
                (($product) ? $product->getSn() : null) . '取消限時搶購完成!'
            );

        return $this->redirect($this->generateUrl('producttl'));
    }
}

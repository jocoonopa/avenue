<?php

namespace Woojin\OrderBundle\Controller\Order;

use Doctrine\ORM\EntityManager;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\GoodsBundle\Entity\Description;
use Woojin\GoodsBundle\Entity\Brief;

use Woojin\StoreBundle\Event\PurchaseEvent;
use Woojin\StoreBundle\StoreEvents;

use Woojin\UserBundle\Entity\User;
use Woojin\UserBundle\Entity\AvenueClue;

use Woojin\Utility\Avenue\Avenue;

/**
 * In controller.
 *
 * 把現有和 $em 的耦合解離出來
 *
 * @Route("")
 */
class InController extends Controller
{
    /**
     * @Route("/purchase", name="admin_purchase", options={"expose"=true})
     * @Method("POST")
     */
    public function purchaseAction(Request $request)
    {       
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user)) {
            throw new \Exception('Session timeout');
        }

        $ids = array();

        $options = $this->getOptions($request, $em, $user);
        $options['user'] = $user;

        $sculper = $this->get('sculper.clue');
        $sculper->initPurchaseIn();

        $dispatcher = $this->get('event_dispatcher');
        
        $event = new PurchaseEvent($options);
        $dispatcher->dispatch(StoreEvents::STORE_PURCHASE_IN, $event);

        $products = $event->getProducts();

        try {
            foreach ($products as $product) {
                $em->persist($product);
            }

            foreach ($event->getOpes() as $ope) {
                $em->persist($ope);
            }

            $em->flush();

            foreach ($products as $product) {
                $product->setSn($product->genSn($user->getStore()->getSn()));
            }

            $imgFactory = $this->get('factory.img');          
            $imgFactory->create(array(
                'file' => $request->files->get('img'),
                'products' => $products,
                'user' => $user 
            ));

            if ($img = $imgFactory->getImg()) {
                $em->persist($img);
            }
           
            $desimgFactory = $this->get('factory.desimg');
            $desimgFactory->create(array(
                'file' => $request->files->get('desimg'),
                'products' => $products,
                'user' => $user 
            ));

            if ($desimg = $desimgFactory->getDesimg()) {
                $em->persist($desimg);
            }

            foreach ($products as $product) {
                $product
                    ->setSn($product->genSn($user->getStore()->getSn()))
                    ->setImg($img)
                    ->setDesimg($desimg)
                ;

                $em->persist($product);
            }

            foreach ($event->getOrders() as $order) {
                $clue = new AvenueClue;
                $clue
                    ->setUser($user)
                    ->setContent($sculper->setAfter($order)->getContent())
                ;

                $em->persist($clue);
                $em->persist($order);
            }

            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e){
            $em->getConnection()->rollback();

            throw $e;
        }    

        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        return new JsonResponse($ids);
    }

    protected function getOptions(Request $request, EntityManager $em, User $user)
    {
        $description = new Description(stripslashes($request->request->get('description', '<p>待補</p>')));
        $em->persist($description);

        $brief = new Brief($request->request->get('brief', '<p>待補</p>'));
        $em->persist($brief);

        return $options = array(
            'amount' => $request->request->get('purchase_amount', 1),
            'name' => $request->request->get('name'),
            'model' => $request->request->get('model'),
            'price' => $request->request->get('price'),
            'webPrice' => $request->request->get('webPrice'),
            'cost' => $request->request->get('cost'),
            'seoWord' => $request->request->get('seoWord'),
            'customSn' => $request->request->get('customSn'),
            'orgSn' => $request->request->get('org_sn'),
            'colorSn' => $request->request->get('colorSn'),
            'memo' => $request->request->get('memo'),
            'custom' => $em->getRepository('WoojinOrderBundle:Custom')->findOwnUseTheMobil($user, $request->request->get('mobil', null)),
            'seoSlogan' => $em->find('WoojinGoodsBundle:SeoSlogan', $request->request->get('seoSlogan', 0)),
            'seoSlogan2' => $em->find('WoojinGoodsBundle:SeoSlogan', $request->request->get('seoSlogan2', 0)),
            'brand' => $em->find('WoojinGoodsBundle:Brand', $request->request->get('brand', 0)),
            'level' => $em->find('WoojinGoodsBundle:GoodsLevel', $request->request->get('level', 0)),
            'color' => $em->find('WoojinGoodsBundle:Color', $request->request->get('color', 0)),
            'mt' => $em->find('WoojinGoodsBundle:GoodsMT', $request->request->get('mt', 0)),
            'pattern' => $em->find('WoojinGoodsBundle:Pattern', $request->request->get('pattern', 0)),
            'source' => $em->find('WoojinGoodsBundle:GoodsSource', $request->request->get('source', 0)),
            'status' => $em->find('WoojinGoodsBundle:GoodsStatus', ($request->request->get('isPreSale') == 1) ? Avenue::GS_PRESALE : Avenue::GS_ONSALE),
            'isAllowWeb' => $request->request->get('isAllowWeb', false),
            'isAllowCreditCard' => $request->request->get('isAllowCreditCard', false),
            'isBehalf' => $request->request->get('isBehalf', false),
            'description' => $description,
            'brief' => $brief,
            'categorys' => $em->getRepository('WoojinGoodsBundle:Category')->findByIds($request->request->get('category', array())),
            'user' => $user,
            'orderStatusHandling' => $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_HANDLING),
            'orderStatusComplete' => $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_COMPLETE),
            'orderKindIn' => $em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_IN),
            'orderKindConsign' => $em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_CONSIGN_IN),
            'orderKindFeedback' => $em->find('WoojinOrderBundle:OrdersKind', Avenue::OK_FEEDBACK),
            'paytype' => $em->find('WoojinOrderBundle:PayType', Avenue::PT_CASH)
        );     
    }
}

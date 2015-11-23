<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\OrderBundle\Entity\Orders;
use Woojin\OrderBundle\Entity\Ope;

use Avenue\Adapter\Adapter;
use Woojin\Utility\Avenue\Avenue;

class QueController extends Controller
{
    /**
     * 付款完成通知
     * %數請參考 https://www.allpay.com.tw/Business/payment_fees
     * 
     * @Route("/que/return", name="front_que_return", options={"expose"=true})
     */
    public function returnAction(Request $request)
    {
        $adapter = new Adapter;
        $adapter->init(
            $this->container->getParameter('allpay_hashkey'), 
            $this->container->getParameter('allpay_hashiv'), 
            $this->container->getParameter('allpay_merchantid'), 
            true
        );
        //$adapter->initTest();

        $fp = fopen($request->server->get('DOCUMENT_ROOT') . '/uploads/' . date('Y-m-d H:i:s') . '.txt', 'w');
        fwrite($fp, json_encode($_POST) . "\r\n");

        if (!$adapter->isValid($_POST)) {
            fwrite($fp, "\r\n" . '檢查碼錯誤:' . "\r\n" . $adapter->getCheckVal($_POST) . "\r\n" . $_POST['ChechMacValue']);
            fclose($fp);
            throw new \Exception('valid error');
        }

        fwrite($fp, "\r\n" . '檢查碼正確');

        $post = $request->request;

        if ($post->get('RtnCode') != 1) {
            fwrite($fp, "\r\n" . '狀態碼錯誤');
            fclose($fp);
            return new Response('0|ErrorMessage');
        }

        fwrite($fp, "\r\n" . '狀態碼正確');

        $invoiceSn = $post->get('MerchantTradeNo');

        $em = $this->getDoctrine()->getManager();

        $invoice = $em->getRepository('WoojinOrderBundle:Invoice')->findOneBy(array('sn' => $invoiceSn));
        if (!$invoice) {
            fwrite($fp, "\r\n" . '兄弟我找不到發票');
            fclose($fp);
            return new Response('0|ErrorMessage');
        }

        $invoice
            ->setTradeNo($post->get('TradeNo'))
            ->setPaymentType($post->get('PaymentType'))
            ->setPayAt(new \DateTime($post->get('PaymentDate')))
            ->setStatus(1)
        ;

        $em->persist($invoice);
        $em->flush();

        fwrite($fp, "\r\n" . '應該要成功才對, 這是交易編號' . $invoice->getTradeNo());
        fclose($fp);

        // 發送通知信給店長處理
        $notifier = $this->get('avenue.notifier');
        $notifier->ship($invoice);

        return new Response('1|OK');
    }

    /**
     * @Route("/queCountStat/{key}", name="que_countStat")
     * @Method("GET")
     */
    public function countStatAction(Request $request, $key)
    {
        if ($key !== 'Hiasd9988811ss') {
            $fp = fopen($request->server->get('DOCUMENT_ROOT') . '/cronLog/countStat/fail' . date('Y-m-d') . '.txt', 'a+');
            fwrite($fp, '時間:' . date('Y-m-d H:i:s') . '驗證碼: ' . $key . ', ip:' . $_SERVER['REMOTE_ADDR'] . "\r\n");
            fclose($fp);
            
            throw new \Exception('error');
        }

        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $start = microtime(true);

        $entityNameArray = array(
            'Brand',
            'Pattern',
            'Promotion',
            'GoodsLevel'
        );

        $em = $this->getDoctrine()->getManager();

        /**
         * 所有分類
         * 
         * @var array{\Woojin\GoodsBundle\Entity\Category}
         */
        $categorys = $em->getRepository('WoojinGoodsBundle:Category')->findAll();

        foreach ($entityNameArray as $entityName) {
            $this->setEntityProductCount($entityName, $categorys);
        }

        $executeCost = microtime(true) - $start;

        $fp = fopen($request->server->get('DOCUMENT_ROOT') . '/cronLog/countStat/success' . date('Y-m-d') . '.txt', 'a+');
        fwrite($fp, '時間:' . date('Y-m-d H:i:s') . ', 執行成功，耗費' . $executeCost . '秒' . "\r\n");
        fclose($fp);

        return new Response($executeCost);
    }

    protected function setEntityProductCount($entityName, $categorys) 
    {
        $em = $this->getDoctrine()->getManager();

        $entitys = $em->getRepository('WoojinGoodsBundle:' . $entityName)->findAll();

        foreach ($entitys as $entity) {
            $goodses = $entity->getGoodsPassports();
            $count = 0;
            $womenCount = 0;
            $menCount = 0;
            $secondhandCount = 0;

            foreach ($goodses as $goods) {
                if ($goods->getIsAllowWeb() 
                    && in_array($goods->getStatus()->getId(), array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY))
                ) {
                    foreach ($categorys as $category) {
                        if ($goods->hasCategory($category)) {
                            switch ($category->getId()) 
                            {
                                case Avenue::CT_WOMEN:
                                    $womenCount ++;
                                    break;

                                case Avenue::CT_MEN:
                                    $menCount ++;
                                    break;

                                case Avenue::CT_SECONDHAND:
                                    $secondhandCount ++;
                                    break;
                            }
                        }
                    }

                    $count ++;
                }
            }

            $entity
                ->setCount($count)
                ->setWomenCount($womenCount)
                ->setMenCount($menCount)
                ->setSecondhandCount($secondhandCount)
            ;
            $em->persist($entity);
        }
        
        $em->flush();
    }

    /**
     * 發送每日操作信件, 各店店長收到自己店得所有記錄，
     * 老闆收到各店得信件
     *
     * 1. 五間店 for:
     * [
     *     1. 抓取各店 clue
     *     2. 製作mail
     *     3. 發送給該店店長以及老闆
     * ]
     * 
     * 2. 清除七天前內容
     * 
     * @Route("/que/cluecron/{key}", name="que_cluecron")
     * @Method("GET")
     */
    public function sendClueMail($key)
    {
        if ($key !== 'mk123njaf!@KKKtjioadSSJks') {
            throw new \Exception('Error');
        }

        set_time_limit(0);

        $yesterdat = new \DateTime();
        $yesterdat->modify('-24 hours');

        $em = $this->getDoctrine()->getManager();

        $chiefs = array(
            $em->find('WoojinUserBundle:User', Avenue::USER_CHIEF_Z), 
            $em->find('WoojinUserBundle:User', Avenue::USER_CHIEF_Y), 
            $em->find('WoojinUserBundle:User', Avenue::USER_CHIEF_X), 
            $em->find('WoojinUserBundle:User', Avenue::USER_CHIEF_P), 
            $em->find('WoojinUserBundle:User', Avenue::USER_CHIEF_L),
            $em->find('WoojinUserBundle:User', Avenue::USER_CHIEF_T),
            $em->find('WoojinUserBundle:User', Avenue::USER_STOCK) 
        );

        $boss = $em->find('WoojinUserBundle:User', Avenue::USER_BOSS);
        $manager = $em->find('WoojinUserBundle:User', Avenue::USER_MANAGER);
        $eng = $em->find('WoojinUserBundle:User', Avenue::USER_ENG);

        $notifier = $this->get('avenue.notifier');

        foreach ($chiefs as $chief) {
            $qb = $em->createQueryBuilder();
            $qb
                ->select('ac')
                ->from('WoojinUserBundle:AvenueClue', 'ac')
                ->leftJoin('ac.user', 'u')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->gte('ac.createAt', $qb->expr()->literal($yesterdat->format('Y-m-d'))),
                        $qb->expr()->eq('u.store', $chief->getStore()->getId())
                    )
                )
            ;

            $clues = $qb->getQuery()->getResult();
            $notifier->clue($clues, array($eng, $manager, $boss), $chief->getStore());
            
            unset($clues);
            unset($qb); 
        }

        return new JsonResponse(array('status' => 1));
    }
}

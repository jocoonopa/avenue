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

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Finder\Finder;

class QueController extends Controller
{
    const NUM_PERPAGE = 200;
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

        try {
            $invoice
                ->setTradeNo($post->get('TradeNo'))
                ->setPaymentType($post->get('PaymentType'))
                ->setPayAt(new \DateTime($post->get('PaymentDate')))
                ->setStatus(Avenue::IV_GET)
            ;

            foreach ($invoice->getOrders() as $order) {
                $order
                    ->setPaid($order->getRequired())
                    ->setOrgPaid($order->getOrgRequired())
                    ->setStatus($em->getRepository('WoojinOrderBundle:OrdersStatus')->find(Avenue::OS_COMPLETE))
                ;

                $em->persist($order);
            }

            $em->persist($invoice);
            $em->flush();

            fwrite($fp, "\r\n" . '應該要成功才對, 這是交易編號' . $invoice->getTradeNo());
            fclose($fp);
        } catch (\Exception $e) {
            fwrite($fp, "\r\n" . $e->getMessage() . $invoice->getTradeNo());
            fclose($fp);
        }

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
            $notifier->clue($clues, array($manager, $boss), $chief->getStore());
            
            unset($clues);
            unset($qb); 
        }

        return new JsonResponse(array('status' => 1));
    }

    /**
     * 
     * @Route("/que/inode", name="que_inode")
     * @Method("GET")
     */
    public function inode(Request $request)
    {
        set_time_limit(0);

        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../../');

        $allInode = count($finder);

        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../../../web/img/product');

        $total = 0;

        foreach ($finder as $file) {      
            echo $file->getRealPath() . ":{$total}<br/>";          
            if (!file_exists($file->getRealPath())) {
                continue;
            }
            
            unset($file);

            $total ++;  
        }

        // all: 63477, vendor:11126, src: 810, web: 44922, bundles: 6660, productImg: 32115
        return new Response("all: {$allInode}, total: {$total}");
    }

    /**
     * FLOW OF DESIMG:
     * 1. Fetch collections of desimg
     * 2. Iterate collection
     * 3. Get each path without filename
     * 4. Delete dir (rmdir($file->getRealPath());)
     * 
     * @Route("/que/delete_desimg", name="que_img_delete_desimg")
     * @Method("GET")
     */
    public function deleteUselessDesimg()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $count = $this->fetchCountDesimg(0);
        $total = 0;

        $startAt = microtime(true);

        for ($i = 0; $i <= $count + self::NUM_PERPAGE; $i = $i + self::NUM_PERPAGE) {
            $desimgs = $this->fetchDesimgCollection($i);

            //echo "flag: {$i}<br/>";

            foreach ($desimgs as $desimg) {
                if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $desimg->getPath())) {
                    if ('_____' !== $desimg->getPath()) {
                        // echo 'leave: ' . $desimg->getPath() . "<br/>";
                        // $total ++;
                    }
                    
                    continue;
                }

                $finder = new Finder();
                $path = pathinfo($_SERVER['DOCUMENT_ROOT'] . $desimg->getPath());     
                $finder->files()->in($path['dirname'])->name('des*')->notName(basename($desimg->getPath()));

                echo '<br>m: ' . basename($desimg->getPath()) . '<hr>';

                foreach ($finder as $file) {      
                    $total ++;
                    echo $file->getRealPath() . ":{$total}<br/>";          
                    if (!file_exists($file->getRealPath())) {
                        continue;
                    }
                    
                    unlink($file->getRealPath());               
                    unset($file);
                }
                unset($finder);
                
            }
            
            unset($desimgs);
        }

        return new Response($count . '_desimg_delete_complete_' . $total . ',cost time:' . (microtime(true) - $startAt));
    }

    protected function fetchDesimgCollection($page)
    {
        return new Paginator($this->genFetchDesimgQ($page), $fetchJoinCollection = false);
    }   

    protected function genFetchDesimgQ($page)
    {
        $em = $this->getDoctrine()->getManager();
        
        $date = new \DateTime();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('img')
            ->from('WoojinGoodsBundle:Desimg', 'img')
        ;

        // $qb->andWhere($qb->expr()->andX(
        //     // $qb->expr()->in('gs.id', array(2)),
        //     // $qb->expr()->eq('ok.type', 2),
        //     // $qb->expr()->eq('os.id', 2),
        //     $qb->expr()->lt('gd.updateAt', $qb->expr()->literal($date->modify('-1 months')->format('Y-m-d H:i:s'))) 
        // ));

        return $qb->orderBy('img.id', 'ASC')
                ->setFirstResult($page)
                ->setMaxResults(self::NUM_PERPAGE)
            ;
    }

    protected function fetchCountDesimg()
    {
        return count($this->fetchDesimgCollection(0));
    }
}

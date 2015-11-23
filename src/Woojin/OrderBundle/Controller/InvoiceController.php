<?php

namespace Woojin\OrderBundle\Controller;

//Third Party
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

//Component
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

//Default
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

// Entity
use Woojin\OrderBundle\Entity\Invoice;
use Woojin\StoreBundle\Entity\Store;
use Woojin\Utility\Avenue\Avenue;

/**
 * Invoice controller.
 *
 * @Route("")
 */
class InvoiceController extends Controller
{
    /**
     * 官網訂單列表
     *
     * @Route("/invoice/web", name="admin_invoice_web_index")
     * @Method("GET")
     * @Template()
     */
    public function webIndexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $invoices = $em->getRepository('WoojinOrderBundle:Invoice')->findBy(array('isAllPay' => true), array('id' => 'DESC'));

        return array('invoices' => $invoices);
    }

    /**
     * 到貨完成
     *
     * @Route("/invoice/{id}/giveDone", requirements={"id"="\d+"}, name="admin_invoice_giveDone")
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("PUT")
     */
    public function giveDoneAction(Invoice $invoice)
    {
        $em = $this->getDoctrine()->getManager();

        $invoice->setStatus(Avenue::IV_GIVE_DONE);
        $em->persist($invoice);
        $em->flush();

        $session = $this->get('session');

        $session->getFlashBag()->add('msg', $invoice->getSn() . '到貨確認完成！');

        return $this->redirect($this->get('router')->generate('admin_invoice_web_index'));
    }

    /**
     * 退款確認
     *
     * @Route("/invoice/{id}/cancel", requirements={"id"="\d+"}, name="admin_invoice_cancel")
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("PUT")
     */
    public function cancelAction(Invoice $invoice)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice->setStatus(Avenue::IV_CANCEL);
        $em->persist($invoice);

        $orders = $invoice->getOrders();

        $orderCancel = $em->find('WoojinOrderBundle:OrdersStatus', Avenue::OS_CANCEL);

        $productMoving = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_MOVING);

        $iterator = $orders->getIterator();
        
        while ($iterator->valid()) {
            $order = $iterator->current();
            $order->setStatus($orderCancel);

            $em->persist($order);

            $product = $order->getGoodsPassport();
            $product->setStatus($productMoving);
            
            $em->persist($product);

            $iterator->next();
        }

        $em->flush();

        $session = $this->get('session');

        $session->getFlashBag()->add('msg', $invoice->getSn() . '退款處理完成');

        return $this->redirect($this->get('router')->generate('admin_invoice_web_index'));
    }

    /**
     * 退貨完成
     *
     * @Route("/invoice/{id}/backDone", requirements={"id"="\d+"}, name="admin_invoice_backDone")
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("PUT")
     */
    public function backDoneAction(Invoice $invoice)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice->setStatus(Avenue::IV_BACK_DONE);
        $em->persist($invoice);

        $orders = $invoice->getOrders();

        $onSale = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE);

        $iterator = $orders->getIterator();
        while ($iterator->valid()) {
            $order = $iterator->current();
            $product = $order->getGoodsPassport();
            $product->setStatus($onSale);
            $em->persist($product);

            $iterator->next();
        }

        $em->flush();

        $session = $this->get('session');

        $session->getFlashBag()->add('msg', $invoice->getSn() . '退還貨物接收完成');

        return $this->redirect($this->get('router')->generate('admin_invoice_web_index'));
    }

    /**
     * 拒絕退貨
     *
     * @Route("/invoice/{id}/cancel/reject", requirements={"id"="\d+"}, name="admin_invoice_cancel_reject")
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("PUT")
     */
    public function rejectCancelAction(Invoice $invoice)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice->setStatus(Avenue::IV_GIVE_DONE);
        $em->persist($invoice);

        $em->flush();

        $session = $this->get('session');

        $session->getFlashBag()->add('msg', $invoice->getSn() . '拒絕退貨完成');

        return $this->redirect($this->get('router')->generate('admin_invoice_web_index'));
    }

    /**
     * 對外開放的發票api，內容格式為json，回傳細項參數對應如下:<br/>
     * 1. id : 發票id<br/>
     * 2. store: 店名<br/>
     * 3. createAt: 發票建立時間<br/>
     * 4. updateAt: 發票最後更新時間<br/>
     * 5. hasPrint: 是否已經列印<br/>
     * 6. sn: 統一編號<br/>
     * 
     * 7. custom:客戶<br/>
     *     ->name: 客戶名稱<br/>
     *     ->phone: 客戶電話<br/>
     * 
     * 8. orders: 訂單表, 每筆訂單的內容如下<br/>
     *     -> id: 訂單id<br/>
     *     -> payType: 付款方式<br/>
     *     -> paid: 訂單已付金額<br/>
     *     -> required: 訂單應付金額<br/>
     *     -> status: 訂單狀態<br/>
     *     -> memo: 備註<br/>
     *     
     *     -> goods: 商品<br/>
     *         ---> name: 品名<br/>
     *         ---> sn: 產編<br/>
     *         ---> brand: 品牌<br/>
     *         ---> brandType: 款式<br/>
     *         ---> brandSn: 型號<br/>
     *         ---> orgSn: 原廠包號<br/>
     *         ---> level: 新舊程度<br/>
     *         ---> material: 材質<br/>
     * <br/><br/>
     *
     * Example: /api/invoice/v1/1/0/YourApiKey.json
     * 索引為1 的店家，尚未列印的發票，已 json 格式回傳給用戶端
     * 
     * 
     * @Route(
     *     "/api/invoice/v1/{id}/{hasPrint}/{apiKey}.{_format}", 
     *     requirements={"hasPrint" = "\d+", "id" = "\d+"}, 
     *     name="invoice_api_list",
     *     defaults={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @ParamConverter("store", class="WoojinStoreBundle:Store")
     * @Method("GET")
     *
     *
     * @ApiDoc(
     *  resource=true,
     *  https=true,
     *  description="取得發票列表, 條件為'商店id(store_id)' and '是否已經列印(hasPrint)' ",
     *  requirements={
     *      {"name"="apiKey", "dataType"="string", "required"=true, "description"="Used to validate permission"},
     *      {"name"="_format", "dataType"="string", "required"=true, "description"="Decide type of return MIME, default is json"},
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="The Store serial number, will be use to find data, I don't use 'sotreId' but 'store_id' because Paramconverter will cause error"},
     *      {"name"="hasPrint", "dataType"="boolean", "required"=true, "description"="The Invoice has been printed or not"},
     *  },
     *  statusCodes={
     *    200="Returned when successful",
     *    403="Returned when the ApiKey is not matched to say hello",
     *    404={
     *     "Returned when the ApiKey is not matched",
     *     "Returned when something else is not found"
     *    },
     *    500={
     *     "Please contact author to fix it"
     *    }
     *  }
     * )
     */
    public function apiListAction(Store $store, $hasPrint, $apiKey, $_format)
    {
        // Open on prod
        // if ($this->isValid($apiKey, 'Reebonz')) {
        //     throw new InvalidParameterException('Sorry, the api key is not valid!');
        // }

        /**
         * Invoice entity array 
         * @var array{ object }
         */
        $invoices = $this->getDoctrine()->getRepository('WoojinOrderBundle:Invoice')
            ->findBy(array('store' => $store, 'hasPrint' => $hasPrint));

        /**
         * Return store repo
         * @var array
         */
        $returnData = array();

        /**
         * orderService, use to set invoice in correct foramt
         * @var object
         */
        $orderService = $this->get('order_service');

        // Push data in $returnData
        foreach ($invoices as $invoice) {
           
            $orderService
                ->setInvoiceMeta($tmp = new \stdClass, $invoice)
                ->setInvoiceCustom($tmp->custom = new \stdClass, $invoice->getCustom())
            ;
            
            $tmp->orders = array();

            foreach ($invoice->getOrders() as $order) {
                $orderService
                    ->setInvoiceOrders($tmpOrder = new \stdClass, $order)
                    ->setInvoiceOrdersGoods($tmpOrder->goods = new \stdClass, $order->getGoodsPassport())           
                ;

                array_push($tmp->orders, $tmpOrder);
            }

            array_push($returnData, $tmp);
        }

        return new Response(json_encode($returnData));
    }

    /**
     * 可能要根據store 設定不同的 api key, 否則會有別間店能看到他店發票的問題。
     * 回傳細項說明如下:
     * 1. id : 發票id<br/>
     * 2. store: 店名<br/>
     * 3. createAt: 發票建立時間<br/>
     * 4. updateAt: 發票最後更新時間<br/>
     * 5. hasPrint: 是否已經列印<br/>
     * 6. sn: 統一編號<br/>
     * 
     * 7. custom:客戶<br/>
     *     ->name: 客戶名稱<br/>
     *     ->phone: 客戶電話<br/>
     * 
     * 8. orders: 訂單表, 每筆訂單的內容如下<br/>
     *     -> id: 訂單id<br/>
     *     -> payType: 付款方式<br/>
     *     -> paid: 訂單已付金額<br/>
     *     -> required: 訂單應付金額<br/>
     *     -> status: 訂單狀態<br/>
     *     -> memo: 備註<br/>
     *     
     *     -> goods: 商品<br/>
     *         ---> name: 品名<br/>
     *         ---> sn: 產編<br/>
     *         ---> brand: 品牌<br/>
     *         ---> brandType: 款式<br/>
     *         ---> brandSn: 型號<br/>
     *         ---> orgSn: 原廠包號<br/>
     *         ---> level: 新舊程度<br/>
     *         ---> material: 材質<br/>
     * 
     * @Route(
     *     "/api/invoice/v1/{id}/{apiKey}.{_format}", 
     *     requirements={"id" = "\d+"}, 
     *     name="invoice_api_show",
     *     defaults={"_format"="json"},
     *     options={"expose"=true}
     * )
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("GET")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="根據傳入的 invoice_id 取得單一指定發票(invoice)",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="發票的 id "},
     *      {"name"="apiKey", "dataType"="string", "required"=true, "description"="Used to validate permission"},
     *      {"name"="_format", "dataType"="string", "required"=true, "description"="Decide type of return MIME, default is json"}
     *  },
     *  statusCodes={
     *    200="Returned when successful",
     *    404={
     *     "Returned when something else is not found"
     *    },
     *    500={
     *     "Please contact author to fix it"
     *    }
     *  }
     * )
     */
    public function apiShowAction(Invoice $invoice, $apiKey)
    {
        // Open on prod
        // if ($this->isValid($apiKey, 'Reebonz')) {
        //     throw new InvalidParameterException('Sorry, the api key is not valid!');
        // }

        /**
         * orderService, use to set invoice in correct foramt
         * @var object
         */
        $orderService = $this->get('order_service');

        // 設置發票資訊為制定之格式,
        // 最後轉換為 json 字串回傳
        $orderService
            ->setInvoiceMeta($tmp = new \stdClass, $invoice)
            ->setInvoiceCustom($tmp->custom = new \stdClass, $invoice->getCustom())
        ;

        $tmp->orders = array();

        foreach ($invoice->getOrders() as $order) {
            $orderService
                ->setInvoiceOrders($tmpOrder = new \stdClass, $order)
                ->setInvoiceOrdersGoods($tmpOrder->goods = new \stdClass, $order->getGoodsPassport())           
            ;

            array_push($tmp->orders, $tmpOrder);
        }

        return new Response(json_encode($tmp));
    }

    /**
     * 對外開放的 api, 將根據 id 取得的發票(invoice)之狀態改為 hasPrint=true, <br/>
     * 若有傳入統編{sn}則將 sn 屬性 set 為 {sn},<br/>
     * 更新時間(updateAt)會在 entity 的 lifecyle 自動處理，回傳細項說明如下:<br/><br/>
     * 
     * 1. id : 發票id<br/>
     * 2. store: 店名<br/>
     * 3. createAt: 發票建立時間<br/>
     * 4. updateAt: 發票最後更新時間<br/>
     * 5. hasPrint: 是否已經列印<br/>
     * 6. sn: 統一編號<br/>
     * 
     * @Route(
     *     "/api/invoice/v1/{id}/{apiKey}.{_format}/{sn}", 
     *     requirements={"id" = "\d+"}, 
     *     defaults={"_format"="json", "sn"=""},
     *     name="invoice_api_update", 
     *     options={"expose"=true}
     * )
     * @ParamConverter("invoice", class="WoojinOrderBundle:Invoice")
     * @Method("PUT")
     * 
     * @ApiDoc(
     *  resource=true,
     *  description="更新指定(id)發票(invoice)的 hasPrint 屬性，同時修改更新時間(updateAt)屬性為現在時間，若有傳入統編(Sn)則更新 invoice 的sn屬性",
     *  requirements={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="發票的 id "},
     *      {"name"="apiKey", "dataType"="string", "required"=true, "description"="Used to validate permission"},
     *      {"name"="_format", "dataType"="string", "required"=true, "description"="Decide type of return MIME, default is json"}
     *  },
     *  statusCodes={
     *    200="Returned when successful",
     *    404={
     *     "Returned when something else is not found"
     *    },
     *    500={
     *     "Please contact author to fix it"
     *    }
     *  }
     * )
     */
    public function apiUpdateAction(Invoice $invoice, $apiKey, $sn)
    {
        // 設定發票屬性
        $invoice->setHasPrint(true)->setSn($sn);

        $em = $this->getDoctrine()->getManager();
        $em->persist($invoice);
        $em->flush();

        /**
         * orderService, use to set invoice in correct foramt
         * @var object
         */
        $orderService = $this->get('order_service');

        $orderService->setInvoiceMeta($tmp = new \stdClass, $invoice);

        return new Response(json_encode($tmp));
    }

    /**
     * 取得外部api key
     *
     * @param  [string] $apiKey [客戶端送來的api key]
     * @param  [string] $name [客戶名稱]
     * @return [boolean]
     */
    protected function isValid($apiKey, $name)
    {
        return ($apiKey === md5(date('Y-m-d') . $name));
    }
}

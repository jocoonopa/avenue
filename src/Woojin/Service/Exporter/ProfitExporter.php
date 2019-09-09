<?php

namespace Woojin\Service\Exporter;

use Liuggio\ExcelBundle\Factory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Woojin\OrderBundle\Entity\Orders;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\UserBundle\Entity\User;
use Woojin\Utility\Avenue\Avenue;
use PHPExcel_Style_Fill;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ProfitExporter implements IExporter
{
    protected $service;
    protected $security;
    protected $user;
    protected $index;
    protected $map;
    protected $excel;

    public function __construct(Factory $service, TokenStorage $security)
    {
        $this->service = $service;

        $this->security = $security;

        $this->index = 2;

        /**
         * 使用者實體
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $this->user = $this->security->getToken()->getUser();

        // 1. 設定文件標題
        // 2. 寫入文件欄位名稱
        $this->initHead();
    }

    public function setMap(array $map)
    {
        $this->map = $map;

        return $this;
    }

    public function create($products)
    {
        /**
         * 商品實體陣列, 過濾留下為售出狀態的實體
         *
         * @var array
         */
        $products = array_filter($products, array($this, 'filter'));

        $this->writeDataInFile($products)->addColorAndSum();

        return $this;
    }

    protected function setExcel($excel)
    {
        $this->excel = $excel;

        return $this;
    }

    protected function writeDataInFile($products)
    {
        $excel = $this->excel;

        foreach ($products as $key => $product) {
            if (($key === Avenue::START_FROM) && ($this->index === 2)) {
                $excel->getActiveSheet()->getColumnDimension('A')->setWidth(25);
                $excel->getActiveSheet()->getColumnDimension('C')->setWidth(7);
                $excel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
                $excel->getActiveSheet()->getColumnDimension('E')->setWidth(12);
                $excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                $excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            }

            $sheet = $excel->setActiveSheetIndex(Avenue::START_FROM);

            $order = $this->getColMap($product);

            foreach ($order as $key => $val) {
                $sheet->setCellValue($key . $this->index, $val);
            }

            $this->index ++;
        }

        return $this->setExcel($excel);
    }

    protected function filter($product)
    {
        return ($product->getStatus()->getId() === Avenue::GS_SOLDOUT);
    }

    protected function getTitileMap()
    {
        return array(
            'A1' => '訂單建立時間', // 訂單建立時間
            'B1' => '付清時間', // 付清時間
            'C1' => '品牌', // 品牌
            'D1' => '包名', // 包名
            'E1' => '產編', // 產編
            'F1' => '新舊', // 新舊
            'G1' => '寄賣%數', // 寄賣%數
            'H1' => '寄賣客', // 寄賣客
            'I1' => '寄賣成本', // 寄賣成本
            'J1' => '成本', // 成本
            'K1' => '來源門市', // 來源門市
            'L1' => '付款方式', // 付款方式
            'M1' => '銷售金額', //銷售金額
            'N1' => '含5?', // 含5?
            'O1' => '免5?', // 免5?
            'P1' => '毛利', // 毛利
            'Q1' => '售出人', // 售出人
            'R1' => '活動', // 活動
            'S1' => '備註' // 備註
        );
    }

    protected function getColMap(GoodsPassport $product)
    {
        $order = $product->getValidOutOrder();

        if (!$order) {
            return array(
                'A' => '尚未結清',
                'B' => '尚未結清',
                'C' => '尚未結清',
                'D' => $product->getModel(),
                'E' => '尚未結清',
                'F' => $product->getName(),
                'G' => $product->getSn(),
                'H' => '尚未結清',
                'I' => '尚未結清',
                'J' => '尚未結清',
                'K' => '尚未結清',
                'L' => '尚未結清',
                'M' => '尚未結清',
                'N' => '尚未結清',
                'O' => '尚未結清',
                'P' => '尚未結清',
                'Q' => '尚未結清',
                'R' => '尚未結清',
                'S' => '尚未結清'
            );
        }

        return (
            ($product->isOwn($this->user) && $this->user->getRole()->hasAuth('READ_ORDER_OWN'))
            || $this->user->getRole()->hasAuth('READ_ORDER_ALL')
        )
            ? array(
                'A' => $order->getManualCreatedAt() ? $order->getManualCreatedAt()->format('Y-m-d H:i:s') : null, // 訂單建立時間
                'B' => $order->getOpes()->last()->getDatetime()->format('Y-m-d H:i:s'), // 付清時間
                'C' => ($brand = $product->getBrand()) ? $brand->getName() : null,  // 品牌
                'D' => $product->getName(), // 包名
                'E' => $product->getSn(), // 產編
                'F' => ($level = $product->getLevel()) ? $level->getName() : null, // 新舊
                'G' => $product->getBsoCustomPercentage(), // 寄賣%數
                'H' => false !== ($feedbackOrder = $product->getFeedBackOrder()) ? $feedbackOrder->getCustom()->getName() : null, // 寄賣客
                'I' => $product->getFeedBack(), // 寄賣成本
                'J' => $product->getCostVerifyed($this->user), // 成本
                'K' => $this->map[substr($product->getParent()->getSn(), 0, 1)]->getName(), // 來源門市
                'L' => $order->getPayType() ? $order->getPayType()->getName() : null, // 付款方式
                'M' => $product->getPrice(), // 銷售金額
                'N' => $order->getOrgRequired(), // 含5?
                'O' => $order->getRequired(),  // 免5?
                'P' => ($profit = ($order->getRequired() - $product->getCostVerifyed($this->user))) > 0 ? $profit : 0, // 毛利
                'Q' => $order->getOpes()->last()->getUser()->getUsername(), // 售出人
                'R' => ($activity = $product->getActivity()) ? $activity->getName() : '門市出售', // 活動
                'S' => $order->getMemo() // 備註
            )
            : array(
                'A' => '無閱讀權限',
                'B' => '無閱讀權限',
                'C' => '無閱讀權限',
                'D' => $product->getModel(),
                'E' => '無閱讀權限',
                'F' => $product->getName(),
                'G' => $product->getSn(),
                'H' => '無閱讀權限',
                'I' => '無閱讀權限',
                'J' => '無閱讀權限',
                'K' => '無閱讀權限',
                'L' => '無閱讀權限',
                'M' => '無閱讀權限',
                'N' => '無閱讀權限',
                'O' => '無閱讀權限',
                'P' => '無閱讀權限',
                'Q' => '無閱讀權限',
                'R' => '無閱讀權限',
                'S' => '無閱讀權限',
            );
    }

    public function getResponse()
    {
        // create the writer
        $writer = $this->service->createWriter($this->excel, 'Excel5');
        // create the response
        $response = $this->service->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'avenue_profit_' . time() . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function initHead()
    {
        $excel = $this->service->createPHPExcelObject();

        $sheet = $excel->setActiveSheetIndex(Avenue::START_FROM);

        $excel->getActiveSheet()->setTitle('毛利報表');
        foreach ($this->getTitileMap() as $key => $title) {
            $sheet->setCellValue($key, $title);
        }

        return $this->setExcel($excel);
    }

    public function addColorAndSum()
    {
        $excel = $this->excel;

        $indexNext = ($this->index + 1);

        foreach (array('I', 'J', 'M', 'N', 'O', 'P') as $val) {
            $excel
                ->setActiveSheetIndex(Avenue::START_FROM)
                ->setCellValue($val . $indexNext, "=SUM({$val}1:{$val}" . $this->index . ")");
        }

        // 指回第一個表索引，否則會報錯
        $excel->setActiveSheetIndex(Avenue::START_FROM);

        return $this->setExcel($excel);
    }
}

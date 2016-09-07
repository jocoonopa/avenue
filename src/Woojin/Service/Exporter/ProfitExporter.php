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
            'A1' => '付清時間',
            'B1' => '品牌',
            'C1' => '款式',
            'D1' => '型號',
            'E1' => '顏色',
            'F1' => '包名',
            'G1' => '產編',
            'H1' => '新舊',
            'I1' => '訂價',
            'J1' => '寄賣回扣',
            'K1' => '販售方式',
            'L1' => '銷售金額',
            'M1' => '成本',
            'N1' => '毛利',
            'O1' => '原始銷售金額',
            'P1' => '原始已付',
            'Q1' => '原始毛利',
            'R1' => '售出人',
            'S1' => '備註',
            'T1' => '活動',
            'U1' => '來源單號',
            'V1' => '來源門市'
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
                'S' => '尚未結清',
                'T' => '尚未結清',
                'U' => '尚未結清',
                'V' => '尚未結清'
            );
        }

        return (
            ($product->isOwn($this->user) && $this->user->getRole()->hasAuth('READ_ORDER_OWN'))
            || $this->user->getRole()->hasAuth('READ_ORDER_ALL')
        ) 
            ? array(
                'A' => $order->getOpes()->last()->getDatetime()->format('Y-m-d H:i:s'),
                'B' => ($brand = $product->getBrand()) ? $brand->getName() : null,
                'C' => ($pattern = $product->getPattern()) ? $pattern->getName() : null, // 款式
                'D' => $product->getModel(), // 型號
                'E' => $product->getColorSn(), // 顏色
                'F' => $product->getName(),
                'G' => $product->getSn(),
                'H' => ($level = $product->getLevel()) ? $level->getName() : null,
                'I' => $product->getCostVerifyed($this->user),
                'J' => $product->getFeedBack(),
                'K' => $order->getKind()->getName(),
                'L' => $order->getRequired(),
                'M' => $product->getCostVerifyed($this->user),
                'N' => ($profit = ($order->getRequired() - $product->getCostVerifyed($this->user))) > 0 ? $profit : 0,     
                'O' => $order->getOrgRequired(), // 原始應付
                'P' => ($order->getRequired() === 0) ? 0 : round($order->getOrgRequired() * ($order->getPaid()/$order->getRequired())), //'原始已付'
                'Q' => ($order->getOrgRequired() - $product->getCostVerifyed($this->user)),
                'R' => $order->getOpes()->last()->getUser()->getUsername(),
                'S' => $order->getMemo(),
                'T' => ($activity = $product->getActivity()) ? $activity->getName() : '門市出售',
                'U' => $product->getParent()->getSn(),
                'V' => $this->map[substr($product->getParent()->getSn(), 0, 1)]->getName()
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
                'T' => '無閱讀權限',
                'U' => '無閱讀權限',
                'V' => '無閱讀權限'
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

        $excel
            ->setActiveSheetIndex(Avenue::START_FROM)
            ->setCellValue('L' . $indexNext, "=SUM(L1:L" . $this->index . ")")
        ;

        $excel
            ->setActiveSheetIndex(Avenue::START_FROM)
            ->setCellValue('M' . $indexNext, "=SUM(M1:M" . $this->index . ")")
        ;

        $excel
            ->setActiveSheetIndex(Avenue::START_FROM)
            ->setCellValue('N'.$indexNext, "=SUM(N1:N" . $this->index . ")")
        ;

        $excel
            ->getActiveSheet()
            ->getStyle('E1:E' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => 'BFFF7F'),
                )
            )
        ;

        $excel
            ->getActiveSheet()
            ->getStyle('I1:I' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => '228B22'),
                )
        );

        $excel
            ->getActiveSheet()
            ->getStyle('H1:H' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => 'FF7FFF')
                )
        );

        $excel
            ->getActiveSheet()
            ->getStyle('L1:L' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => '5CF5F5')
                )
        );

        $excel
            ->getActiveSheet()
            ->getStyle('M1:M' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => '7FFF00')
                )
        );

        $excel
            ->getActiveSheet()
            ->getStyle('N1:N' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => 'FFA07A')
                )
        );

        $excel
            ->getActiveSheet()
            ->getStyle('O1:O' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => 'FFE4EAF4')
                )
        );

        $excel
            ->getActiveSheet()
            ->getStyle('P1:P' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => 'FFE4EAF4')
                )
        );

        $excel
            ->getActiveSheet()
            ->getStyle('Q1:Q' . $indexNext)
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => 'FFE4EAF4')
                )
        );

        // 指回第一個表索引，否則會報錯
        $excel->setActiveSheetIndex(Avenue::START_FROM);

        return $this->setExcel($excel);
    }
}
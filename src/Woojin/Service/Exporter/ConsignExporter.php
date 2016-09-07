<?php

namespace Woojin\Service\Exporter;

use Liuggio\ExcelBundle\Factory;
use Symfony\Component\Security\Core\SecurityContext;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\UserBundle\Entity\User;
use Woojin\Utility\Avenue\Avenue;
use PHPExcel_Style_Fill;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @service exporter.consign
 *
 * 匯出寄賣報表
 */
class ConsignExporter implements IExporter
{
    protected $service;
    protected $excel;

    public function __construct(Factory $service)
    {
        $this->service = $service;
    }

    public function create($products)
    {
        $excel = $this->service->createPHPExcelObject();

        $titleMap = $this->getTitileMap();

        foreach ($products as $key => $product) {
            /**
             * $index 為開始有資料的行數(即2)
             * 該值必須隨迭代增加，並且始終比陣列索引 $key 多 2，
             * 因此設定為 $index = $key + 2
             *
             * @var integer
             */
            $index = $key + 2;

            if ($key === Avenue::START_FROM) {
                $sheet = $excel->setActiveSheetIndex(Avenue::START_FROM);
                
                foreach ($titleMap as $key => $val) {
                    $sheet->setCellValue($key, $val);
                }               

                // 進貨時間
                $excel
                    ->getActiveSheet()
                    ->getColumnDimension('A')
                    ->setWidth(25)
                ;

                // 商品名稱
                $excel
                    ->getActiveSheet()
                    ->getColumnDimension('H')
                    ->setWidth(30)
                ;

                // 商品產編
                $excel
                    ->getActiveSheet()
                    ->getColumnDimension('I')
                    ->setWidth(20)
                ;
            } 

            $sheet = $excel->setActiveSheetIndex(Avenue::START_FROM);

            $colData = $this->getColMap($product);

            if (empty($colData)) {
                continue;
            }

            foreach ($colData as $key => $val) {
                $sheet->setCellValue($key . $index, $val);
            }                           
        }

        // 如果不存在索引, 進行標題列和標題的設定即結束生成
        if (!isset($index)) {
            $sheet = $excel->setActiveSheetIndex(Avenue::START_FROM);
    
            foreach ($titleMap as $key => $val) {
                $sheet->setCellValue($key, $val);
            }  

            $excel->getActiveSheet()->setTitle('寄賣報表');
            $excel->setActiveSheetIndex(Avenue::START_FROM); 
            
            return $this;
        }

        // 成本加總
        $excel
            ->setActiveSheetIndex(Avenue::START_FROM)
            ->setCellValue('K' . ($index + 1), "=SUM(K1:K" . $index . ")")
        ;

        // 成本
        $excel
            ->getActiveSheet()
            ->getStyle("K1:K" . ($index + 1))
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => 'cc6a08')
                )
            )
        ;

        // 定價
        $excel
            ->getActiveSheet()
            ->getStyle("J1:J".($index + 1) )
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => '7F7FFF')
                )
            )
        ;

        // 型號
        $excel
            ->getActiveSheet()
            ->getStyle("D1:D" . ($index + 1))
            ->getFill()
            ->applyFromArray(
                array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'startcolor' => array('argb' => '5CF5F5')
                )
            )
        ;

        $excel->getActiveSheet()->setTitle('寄賣報表');
        $excel->setActiveSheetIndex(Avenue::START_FROM); 
        
        return $this;
    }

    protected function setExcel($excel)
    {
        $this->excel = $excel;

        return $this;
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
            'avenue_consign_stock_' . time() . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function getTitileMap()
    {
        return array(
            'A1' => '進貨時間',
            'B1' => '品牌',
            'C1' => '款式',
            'D1' => '型號',
            'E1' => '顏色',
            'F1' => '序號',
            'G1' => '新舊',
            'H1' => '商品名稱',
            'I1' => '商品產編',
            'J1' => '定價',
            'K1' => '成本',
            'L1' => '客戶',
            'M1' => '自定義內碼'
        );
    }

    protected function getColMap(GoodsPassport $product)
    {
        return array(
                'A' => '無閱讀權限',
                'B' => '無閱讀權限',
                'C' => '無閱讀權限',
                'D' => $product->getModel(),
                'E' => '無閱讀權限',
                'F' => '無閱讀權限',
                'G' => '無閱讀權限',
                'H' => $product->getName(),
                'I' => $product->getSn(),
                'J' => '無閱讀權限',
                'K' => '無閱讀權限',
                'L' => '無閱讀權限',
                'M' => '無閱讀權限'
            );
    }
}
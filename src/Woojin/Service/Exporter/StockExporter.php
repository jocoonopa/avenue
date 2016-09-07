<?php

namespace Woojin\Service\Exporter;

use Liuggio\ExcelBundle\Factory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\UserBundle\Entity\User;
use Woojin\Utility\Avenue\Avenue;
use PHPExcel_Style_Fill;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @service exporter.stock
 *
 * 匯出庫存格式報表
 */
class StockExporter implements IExporter
{
    protected $service;

    protected $security;

    protected $excel;

    public function __construct(Factory $service, TokenStorage $security)
    {
        $this->service = $service;

        $this->security = $security;
    }

    public function create($products)
    {
        $excel = $this->service->createPHPExcelObject();

        $titleMap = $this->getTitileMap();

        $user = $this->security->getToken()->getUser();

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

            $colData = $this->getColMap($product, $user);

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

            $excel->getActiveSheet()->setTitle('進貨報表');
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

        $excel->getActiveSheet()->setTitle('進貨報表');
        $excel->setActiveSheetIndex(Avenue::START_FROM); 
        
        return $this->setExcel($excel);
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
            'avenue_stock_' . time() . '.xls'
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

    protected function getColMap(GoodsPassport $product, User $user)
    {
        return (
            ($product->isOwn($user) && $user->getRole()->hasAuth('READ_PRODUCT_OWN'))
            || $user->getRole()->hasAuth('READ_PRODUCT_ALL')
        ) 
            ? array(
                'A' => ($product->getCreatedAt()) ? $product->getCreatedAt()->format('Y-m-d H:i:s') : null,
                'B' => ($brand = $product->getBrand()) ? $brand->getName() : null,
                'C' => ($pattern = $product->getPattern()) ? $pattern->getName() : null,
                'D' => $product->getModel(),
                'E' => (($color = $product->getColor()) ? $color->getName() . '-' : null) . $product->getColorSn(),
                'F' => $product->getOrgSn(),
                'G' => ($level = $product->getLevel()) ? $level->getName() : null,
                'H' => $product->getName(),
                'I' => $product->getSn(),
                'J' => $product->getPrice(),
                'K' => $product->getCostVerifyed($user),
                'L' => ($consigner = $product->getCustom()) ? $consigner->getExcelFormatData() : null,
                'M' => $product->getCustomSn()
            )
            : array(
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
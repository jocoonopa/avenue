<?php

namespace Woojin\Service\Exporter;

use Liuggio\ExcelBundle\Factory;
use PHPExcel_Style_Fill;
use Woojin\Utility\Avenue\Avenue;
use Woojin\StoreBundle\Entity\Auction;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BsoProfitExporter implements IExporter
{
    protected $service;
    protected $excel;

    public function __construct(Factory $service)
    {
        $this->setService($service);
    }

    /**
     * @override
     */
    public function create($auctions)
    {
        ini_set('memory_limit', '512M');

        $this->initHead()->writeDataInFile($auctions);

        return $this;
    }

    protected function writeDataInFile($auctions)
    {
        $index = 2;

        foreach ($auctions as $key => $auction) {
            $sheet = $this->excel->setActiveSheetIndex(Avenue::START_FROM);

            $order = $this->getColMap($auction);

            foreach ($order as $key => $val) {
                $sheet->setCellValue($key . $index, $val);
            }

            $index ++;
        }

        return $this;
    }

    public function getResponse()
    {
        $writer = $this->service->createWriter($this->excel, 'Excel5');
        $response = $this->service->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'bso_profit_' . time() . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function setService(Factory $service)
    {
        $this->service = $service;

        return $this;
    }

    protected function initHead()
    {
        $excel = $this->service->createPHPExcelObject();
        $this->setExcel($excel);

        $sheet = $this->excel->setActiveSheetIndex(Avenue::START_FROM);

        $this->excel->getActiveSheet()->setTitle('BSO毛利報表');
        foreach ($this->getTitileMap() as $key => $title) {
            $sheet->setCellValue($key, $title);
        }

        return $this;
    }

    protected function getTitileMap()
    {
        return array(
            'A1' => '產編',
            'B1' => '品名',
            'C1' => '序號',
            'D1' => '品牌',
            'E1' => '顏色',
            'F1' => '來源門市',
            'G1' => '賣家姓名',
            'H1' => '賣家手機',
            'I1' => '買家姓名',
            'J1' => '買家手機',
            'K1' => '競拍價',
            'L1' => '客戶毛利比',
            'M1' => '門市毛利比',
            'N1' => 'BSO毛利比',
            'O1' => '客戶分潤(含稅)',
            'P1' => '客戶分潤(不含稅)',
            'Q1' => '門市分潤',
            'R1' => 'BSO分潤',
            'S1' => '建立時間',
            'T1' => '建立人員',
            'U1' => '售出時間',
            'V1' => '販售操作人員',
            'W1' => '銷售時間更新次數',
            'X1' => '銷售記錄更新次數',
            'Y1' => '歷史記錄'
        );
    }

    protected function getColMap(Auction $auction)
    {
        return array(
            'A' => $auction->getProductSn(),
            'B' => $auction->getProductName(),
            'C' => $auction->getProductOrgSn(),
            'D' => $auction->getProductBrandName(),
            'E' => $auction->getProductColorName(),
            'F' => $auction->getCreateStoreName(),
            'G' => $auction->getSellerName(),
            'H' => $auction->getSellerMobil(),
            'I' => $auction->getBuyerName(),
            'J' => $auction->getBuyerMobil(),
            'K' => $auction->getPrice(),
            'L' => $auction->getCustomPercentage(),
            'M' => $auction->getStorePercentage(),
            'N' => $auction->getBsoPercentage(),
            'O' => $auction->getCustomProfit(),
            'P' => $auction->getCustomProfit(true),
            'Q' => $auction->getStoreProfit(),
            'R' => $auction->getBsoProfit(),
            'S' => $auction->getCreateAt()->format('Y-m-d H:i:s'),
            'T' => $auction->getCreaterName(),
            'U' => $auction->getSoldAtString(),
            'V' => $auction->getBsserName(),
            'W' => $auction->getSoldAtUpdateCount(),
            'X' => $auction->getSoldUpdateCount(),
            'Y' => $auction->getMemo(true),
        );
    }

    /**
     * Get the value of Excel
     *
     * @return mixed
     */
    public function getExcel()
    {
        return $this->excel;
    }

    /**
     * Set the value of Excel
     *
     * @param mixed excel
     *
     * @return self
     */
    public function setExcel($excel)
    {
        $this->excel = $excel;

        return $this;
    }

}

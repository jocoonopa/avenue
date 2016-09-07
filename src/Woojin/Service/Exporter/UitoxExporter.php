<?php

namespace Woojin\Service\Exporter;

use Liuggio\ExcelBundle\Factory;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpFoundation\JsonResponse;

use Woojin\OrderBundle\Entity\Orders;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\UserBundle\Entity\User;
use Woojin\Utility\Avenue\Avenue;
use Woojin\Utility\Helper\ZipHelper;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use PHPExcel_Style_Fill;

class UitoxExporter implements IExporter
{
    protected $service;
    protected $security;
    protected $zipper;
    protected $user;
    protected $zipPaths;
    protected $excel;

    public function __construct(Factory $service, TokenStorage $security, ZipHelper $zipper)
    {
        $this->service = $service;

        $this->security = $security;

        $this->zipper = $zipper;

        $this->user = $this->security->getToken()->getUser();
    }

    public function create($products)
    {
        $excel = $this->service->createPHPExcelObject();

        $titleMap = $this->getTitileMap();

        $products = array_filter($products, array($this, 'filter'));

        $sheet = $excel->setActiveSheetIndex(Avenue::START_FROM);
                
        foreach ($titleMap as $key => $val) {
            $sheet->setCellValue($key, $val);
        }   

        /**
         * $index 為開始有資料的行數(即2)
         * 該值必須隨迭代增加，並且始終比陣列索引 $key 多 2，
         * 因此設定為 $index = $key + 2
         *
         * @var integer
         */
        $index = 2;

        foreach ($products as $key => $product) {
            $sheet = $excel->setActiveSheetIndex(Avenue::START_FROM);

            foreach ($this->getColMap($product) as $key => $val) {
                $sheet->setCellValue($key . $index, $val);
            }     

            $index ++;                      
        }

        return $this->setExcel($excel);
    }

    protected function setExcel($excel)
    {
        $this->excel = $excel;

        return $this;
    }

    public function zipImg(array $products)
    {
        $filesGroup = array(array());

        $this->zipPaths = array();

        // 10個商品一組
        foreach ($products as $key => $product) {
            if (!isset($filesGroup[floor($key/10)])) {
                $filesGroup[floor($key/10)] = array();
            }

            $this->addInGroup($product, $filesGroup[floor($key/10)]);
        }

        foreach ($filesGroup as $key => $group) {
            $outputPath = $this->getOutputPath($key);
            
            $this->zipper->create($group, $this->getOutputPath($key), true);

            $this->addZipPaths($outputPath);
        }

        return $this;
    }

    public function zipNoborderImg(array $products)
    {
        $filesGroup = array(array());

        $this->zipPaths = array();

        // 10個商品一組
        foreach ($products as $key => $product) {
            if (!isset($filesGroup[floor($key/10)])) {
                $filesGroup[floor($key/10)] = array();
            }

            $this->addInNoborderGroup($product, $filesGroup[floor($key/10)]);
        }

        foreach ($filesGroup as $key => $group) {
            $outputPath = $this->getOutputPath($key);
            
            $this->zipper->create($group, $this->getOutputPath($key), true);

            $this->addZipPaths($outputPath);
        }

        return $this;
    }

    public function getZipResponse()
    {
        return new JsonResponse($this->zipPaths);
    }

    protected function getTitileMap()
    {
        return array(
            'A1' => '廠商自用料號',
            'B1' => 'EAN',
            'C1' => '*商品名稱',
            'D1' => '顏色',
            'E1' => '尺寸',
            'F1' => '同樣商品不同顏色尺寸ID',
            'G1' => '市價',
            'H1' => '*售價',
            'I1' => '賣點1',
            'J1' => '賣點2',
            'K1' => '賣點3',
            'L1' => '賣點4',
            'M1' => '賣點5',
            'N1' => '*主商品圖(寬360以上)',
            'O1' => '商品文案1',
            'P1' => '商品文案2',
            'Q1' => '商品文案3',
            'R1' => '商品圖片',
            'S1' => '是否有間隙',
            'T1' => '本商品規格',
            'U1' => '本商品標準配備',
            'V1' => '*是否有保固',
            'W1' => '年',
            'X1' => '月',
            'Y1' => '*是否有維修保固資訊',
            'Z1' => '維修網址',
            'AA1' => '維修電話',
            'AB1' => '維修地址',
            'AC1' => '箱入數',
            'AD1' => '最小進貨量',
            'AE1' => '商品產地'
        );
    }

    protected function getColMap(GoodsPassport $product)
    {
        return array(
            'A' => $product->getSn(),
            'B' => '',
            'C' => $product->getSeoName(),
            'D' => ($color = $product->getColor()) ? $color->getName() : '',
            'E' => '',
            'F' => '',
            'G' => $product->getPromotionPrice(true),
            'H' => $product->getPromotionPrice(true),
            'I' => ($brief = $product->getBrief()) ? $brief->getOneBrief(1) : '',
            'J' => ($brief = $product->getBrief()) ? $brief->getOneBrief(2) : '',
            'K' => ($brief = $product->getBrief()) ? $brief->getOneBrief(3) : '',
            'L' => ($brief = $product->getBrief()) ? $brief->getOneBrief(4) : '',
            'M' => ($brief = $product->getBrief()) ? $brief->getOneBrief(5) : '',
            'N' => ($img = $product->getImg()) ? $img->getName() : '',
            // 'O' => ($description = $product->getDescription()) ? $description->getContent() : '',
            'O' => '',
            'P' => '',
            'Q' => '',
            'R' => ($desimg = $product->getDesimg()) ? $desimg->getName() : '',
            'S' => '',
            'T' => ($description = $product->getDescription()) ? $this->trimInlineCss($description->getContent()) : '',  //$product->getSpec($product),
            'U' => '',
            'V' => 'N',
            'W' => '',
            'X' => '',
            'Y' => 'Y',
            'Z' => '',
            'AA' => '0229401997',
            'AB' => '新北市中和區興南路一段3號',
            'AC' => 1,
            'AD' => 1,
            'AE' => ''
        );
    }

    /** 
     * trimInlineCss ()
     * Remove the inline CSS styles for HTML tags.
     *
     * @author   Junaid Atari <mj.atari@gmail.com>
     * @version  1.0
     * @param    string   $subject    String to remove styles.
     * @return   string   Filtered HTML string.
     */
    protected function trimInlineCss($subject)
    {
        //** Return if invalid type given.
        if (!is_string($subject) || trim($subject) == '') {
            return null;
        }       
        
        //** Create the anonymous function on Runtime.
        $cr = create_function('$matches', 'return str_replace($matches[2], "", $matches[0]);');
        
        //** Pattern copyright 2011 Junaid Atari
        //** Return with Regex, only find the style=".*" attribute of any tag
        //** and replace using callback.
        return preg_replace_callback('/(<[^>]+( style=".*").*>)/iU', $cr, $subject);
    }

    protected function filter($product)
    {
        return (
            $product->getStatus()->getId() !== Avenue::GS_SOLDOUT 
            && $product->getIsAllowWeb() === true
            && (($product->isOwn($this->user) && $this->user->getRole()->hasAuth('READ_PRODUCT_OWN') && $this->user->getRole()->hasAuth('READ_COST_OWN'))
                || ($this->user->getRole()->hasAuth('READ_PRODUCT_ALL') && $this->user->getRole()->hasAuth('READ_COST_ALL')))
        );
    }

    protected function addInGroup(GoodsPassport $product, array &$files)
    {
        if ($img = $product->getImg()) {
            $files[] = $_SERVER['DOCUMENT_ROOT'] . $img->getPath();
        }
        
        if ($desimg = $product->getDesimg()) {
            $files[] = $_SERVER['DOCUMENT_ROOT'] . $desimg->getPath();
        }
    }

    protected function addInNoborderGroup(GoodsPassport $product, array &$files)
    {
        if ($img = $product->getImg()) {
            $files[] = $img->getPathNoBorder(true);
        }
    }

    protected function getOutputPath($key)
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/img/product/' . $this->user->getStore()->getSn() . '/'. $this->user->getUsername() . '_uitox_' . $key . '.zip'; 
    }

    protected function addZipPaths($path)
    {
        $this->zipPaths[] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);

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
            'uitox_avenue.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}
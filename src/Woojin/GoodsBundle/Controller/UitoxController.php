<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use PHPExcel_Style_Fill;

/**
 * Uitox controller.
 *
 * @Route("/uitox")
 */
class UitoxController extends Controller
{
    /**
     * @Route("", name="uitox_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/combine", name="uitox_combine")
     * @Method("POST")
     */
    public function combineAction(Request $request)
    {
        if (!array_key_exists('main-data', $_FILES) || !array_key_exists('main-price', $_FILES) || !array_key_exists('main-stock', $_FILES)) {
            return new Response('請重新檢查是否有上傳檔案');
        }

        if (count($_FILES['main-data']) !== count($_FILES['main-price']) || count($_FILES['main-price']) !== count($_FILES['main-stock'])) {
            return new Reponse('price 和 data 兩個數量不一樣喔!');
        }

        if (!is_array($_FILES['main-data']['tmp_name']) || !is_array($_FILES['main-price']['tmp_name']) || !is_array($_FILES['main-stock']['tmp_name'])) {
            return new Response('請重新檢查是否有上傳檔案');
        }

        ini_set('memory_limit', '512M');

        // Generate response
        $response = new Response();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $dir = __DIR__ . '/../../../../web/csv/uitox/' . $user->getId();

        $mainDataFileName = 'temp_main_data.csv';
        
        $mainPriceFileName = 'temp_main_price.csv';

        $mainStockFileName = 'temp_main_stock.csv';

        $uitoxExcelGenerator = $this->get('uitox.excel.generator');

        $displayName = 'uitox-' . $user->getStore()->getName() . '-' . date('Y-m-d') . '.xls';
        
        $outputPath = $uitoxExcelGenerator
            ->joinFiles($_FILES['main-data']['tmp_name'], $dir . '/' . $mainDataFileName)
            ->joinFiles($_FILES['main-price']['tmp_name'], $dir . '/'  . $mainPriceFileName)
            ->joinFiles($_FILES['main-stock']['tmp_name'], $dir . '/' . $mainStockFileName)
            ->genWithYahooCsv($dir . '/' . $mainDataFileName, $dir . '/' . $mainPriceFileName, $dir . '/' . $mainStockFileName)
        ;
        
        return $uitoxExcelGenerator->download($response, $outputPath, $displayName);
    }
}

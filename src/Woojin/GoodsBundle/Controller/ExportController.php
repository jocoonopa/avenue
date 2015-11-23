<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Export controller.
 *
 * 報表的匯出無視page 和 perpage 參數
 *
 * @Route("/export")
 */
class ExportController extends Controller
{
    /**
     * 庫存格式報表
     * 
     * @Route("/stock", name="admin_export_stock", options={"expose"=true})
     * @Method("POST")
     */
    public function stockAction(Request $request)
    {       
        /**
         * 商品搜尋者
         * 
         * @var \Woojin\Service\Finder\ProductFinder
         */
        $finder = $this->get('product.finder');
        $request->request->set('page', 1);
        $request->request->set('perpage', 1000000);
        $finder->find($request);

        /**
         * 匯出庫存報表格式服務
         * 
         * @var \Woojin\Service\Exporter\StockExporter
         */
        $exporter = $this->get('exporter.stock');
        $exporter->create($finder->getData());

        return $exporter->getResponse();
    }

    /**
     * 毛利格式報表
     * 
     * @Route("/profit", name="admin_export_profit", options={"expose"=true})
     * @Method("POST")
     */
    public function profitAction(Request $request)
    {      
        /**
         * 商品搜尋者
         * 
         * @var \Woojin\Service\Finder\ProductFinder
         */ 
        $finder = $this->get('product.finder');
        $request->request->set('page', 1);
        $request->request->set('perpage', 1000000);
        $finder->find($request);

        /**
         * 匯出毛利報表格式服務
         * 
         * @var \Woojin\Service\Exporter\StockExporter
         */
        $exporter = $this->get('exporter.profit');
        $exporter->create($finder->getData())->addColorAndSum();

        return $exporter->getResponse();
    }

    /**
     * 優達克斯報表
     * 
     * @Route("/uitox", name="admin_export_uitox", options={"expose"=true})
     * @Method("POST")
     */
    public function uitoxAction(Request $request) 
    {
        /**
         * 商品搜尋者
         * 
         * @var \Woojin\Service\Finder\ProductFinder
         */ 
        $finder = $this->get('product.finder');
        $request->request->set('perpage', 50);
        $finder->find($request);

        /**
         * 匯出優達克斯報表格式服務
         * 
         * @var \Woojin\Service\Exporter\UitoxExporter
         */
        $exporter = $this->get('exporter.uitox');

        // 因為 html 那邊用了 JSON.stringify, 丟過來會產生多餘的 '\', 因此這邊要用 str_replace 濾掉以防錯誤
        $excludes = json_decode(str_replace('\\', '', $request->request->get('exclude')), true);
        
        $data = $this->excludeData($finder->getData(), $excludes);
        
        $exporter->create($data);

        return $exporter->getResponse();
    }

    /**
     * 取得壓縮圖檔路徑
     * 
     * @Route("/uitox_zip", name="admin_export_uitox_zip", options={"expose"=true})
     * @Method("POST")
     */
    public function uitoxZipAction(Request $request)
    {
        /**
         * 商品搜尋者
         * 
         * @var \Woojin\Service\Finder\ProductFinder
         */ 
        $finder = $this->get('product.finder');
        $request->request->set('perpage', 50);
        $finder->find($request);

        // 因為 html 那邊用了 JSON.stringify, 丟過來會產生多餘的 '\', 因此這邊要用 str_replace 濾掉以防錯誤
        $excludes = json_decode(str_replace('\\', '', $request->request->get('exclude')), true);
        
        $data = $this->excludeData($finder->getData(), $excludes);

        /**
         * 匯出優達克斯報表格式服務
         * 
         * @var \Woojin\Service\Exporter\UitoxExporter
         */
        $exporter = $this->get('exporter.uitox');        

        $exporter->zipImg($data);

        return $exporter->getZipResponse();
    }

    /**
     * 取得壓縮圖檔路徑
     * 
     * @Route("/noborder_zip", name="admin_export_noborder_zip", options={"expose"=true})
     * @Method("POST")
     */
    public function noborderZipAction(Request $request)
    {
        /**
         * 商品搜尋者
         * 
         * @var \Woojin\Service\Finder\ProductFinder
         */ 
        $finder = $this->get('product.finder');

        $imgFactory = $this->get('factory.img');

        $request->request->set('perpage', 50);
        $finder->find($request);

        // 因為 html 那邊用了 JSON.stringify, 丟過來會產生多餘的 '\', 因此這邊要用 str_replace 濾掉以防錯誤
        $excludes = json_decode(str_replace('\\', '', $request->request->get('exclude')), true);
        
        $data = $this->excludeData($finder->getData(), $excludes);

        /**
         * 匯出優達克斯報表格式服務
         * 
         * @var \Woojin\Service\Exporter\UitoxExporter
         */
        $exporter = $this->get('exporter.uitox');  

        foreach ($data as $product) {
            $img = $product->getImg();
            
            if ($img && !file_exists($img->getPathNoBorder(true))) {
                $imgFactory->createRemoveBorder($img);
            }
        }      

        $exporter->zipNoborderImg($data);

        return $exporter->getZipResponse();
    }

    protected function excludeData(array $data, array $excludes)
    {
        foreach ($data as $key => $product) {
            if (in_array($product->getId(), $excludes)) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}

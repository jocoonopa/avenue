<?php

namespace Woojin\Utility\YahooApi;

use Woojin\Utility\Helper\CurlHelper;
use Woojin\Utility\YahooApi\Helper\Preparer;
use Woojin\Utility\YahooApi\Yahoo;
use Woojin\Utility\YahooApi\Provider\UpdateMainParameterProvider;

use Woojin\GoodsBundle\Entity\GoodsPassport;

/**
 * 使用無規格上傳
 */
class Client
{
    protected $preparer;
    protected $provider;
    protected $curler;
    protected $url;
    protected $queryParameters;

    public function __construct(CurlHelper $curler, $apiKey, $secret)
    {
        $this->curler = $curler;
        $this->preparer = new Preparer($curler, $apiKey, $secret);
    }

    public function setProvider()
    {
        $this->provider = new UpdateMainParameterProvider($this);

        return $this;
    }

    public function getProvider()
    {
        if (!$this->provider) {
            $this->setProvider();
        }

        return $this->provider;
    }

    public function getMain(GoodsPassport $product)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v3/Product/GetMain';

        $factoryName = 'GetMainParameterFactory';

        $parameterFactory = $this->getParameterFactory($factoryName);
        $parameterFactory->create($product);

        $this->queryParameters = $this->preparer->getParameter($parameterFactory->get());

        return $this->retry();
    }

    public function mallCategoryGet(array $r)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/MallCategory/Get';

        $factoryName = 'MallCategoryGetParameterFactory';

        $parameterFactory = $this->getParameterFactory($factoryName);
        $parameterFactory->create($r);

        $this->queryParameters = $this->preparer->getParameter($parameterFactory->get());

        return $this->retry();
    }

    public function storePaymentGet()
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/StorePayment/Get';

        $factoryName = 'StorePaymentParameterFactory';

        $parameterFactory = $this->getParameterFactory($factoryName);
        $parameterFactory->create();

        $this->queryParameters = $this->preparer->getParameter($parameterFactory->get());

        return $this->retry();
    }

    public function storeShippingGet()
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/StoreShipping/Get';

        $factoryName = 'StoreShippingParameterFactory';

        $parameterFactory = $this->getParameterFactory($factoryName);
        $parameterFactory->create();

        $this->queryParameters = $this->preparer->getParameter($parameterFactory->get());

        return $this->retry();
    }

    public function storeCategoryGet()
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/StoreCategory/Get';

        $factoryName = 'StoreCategoryGetParameterFactory';

        $parameterFactory = $this->getParameterFactory($factoryName);
        $parameterFactory->create();

        $this->queryParameters = $this->preparer->getParameter($parameterFactory->get());

        return $this->retry();
    }

    /**
     * 上傳商品驗證
     * 
     * @param  GoodsPassport $product
     * @return JSON                
     */
    public function submitVerifyMain(\stdClass $r)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v2/Product/SubmitVerifyMain';

        return $this->request($r, 'SubmitVerifyMainParameterFactory');
    }

    /**
     * 上傳商品
     * 
     * @param  GoodsPassport $product
     * @return JSON                
     */
    public function submitMain(\stdClass $r)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v2/Product/SubmitMain';

        return $this->request($r, 'SubmitMainParameterFactory');
    }

    /**
     * 更新商品資料
     * 
     * @param  GoodsPassport $product
     * @return JSON                
     */
    public function updateMain(\stdClass $r)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/Product/UpdateMain';

        return $this->request($r, 'UpdateMainParameterFactory');
    }

    /**
     * 上傳圖片
     * 
     * @param  GoodsPassport $product
     * @return JSON                
     */
    public function uploadImage(GoodsPassport $product)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/Product/UploadImage';

        return $this->postRequest($product, 'UploadImageParameterFactory');
    }

    /**
     * 更新圖片
     * 
     * @param  GoodsPassport $product
     * @return JSON                
     */
    public function updateImage(GoodsPassport $product)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/Product/UpdateImage';

        return $this->postRequest($product, 'UpdateImageParameterFactory');
    }

    /**
     * 上架商品
     * 
     * @param  GoodsPassport $product
     * @return JSON                
     */
    public function online(GoodsPassport $product)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/Product/Online';

        return $this->request($product, 'OnlineParameterFactory');
    }

    /**
     * 下架商品
     * 
     * @param  GoodsPassport $product
     * @return JSON                
     */
    public function offline($input)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/Product/Offline';

        $input = $this->filterDeleteAndOffline($input);

        return $this->request($input, 'OfflineParameterFactory');
    }

    /**
     * 刪除商品
     * 
     * @param  GoodsPassport|array(GoodsPassport) $input
     * @return JSON                
     */
    public function delete($input)
    {
        $this->url = Yahoo::YAHOO_API_URL . '/v1/Product/Delete';

        $input = $this->filterDeleteAndOffline($input);
        
        return $this->request($input, 'DeleteParameterFactory');
    }

    /**
     * 發送請求
     * 
     * @param  object|integer|string|array $val  
     * @param  string $factoryName
     * @return json                    
     */
    protected function request($val, $factoryName)
    {
        $parameterFactory = $this->getParameterFactory($factoryName);
        $parameterFactory->create($val);

        $this->queryParameters = $this->preparer->getParameter($parameterFactory->get());

        return $this->retry();
    }

    /**
     * 發送請求(POST)
     * 
     * @param  object|integer|string|array $val  
     * @param  string $factoryName
     * @return json                    
     */
    protected function postRequest($product, $factoryName)
    {
        $parameterFactory = $this->getParameterFactory($factoryName);
        $parameterFactory->create($product);

        $this->queryParameters = $this->preparer->getParameter(
            $parameterFactory->get(), 
            array(
                'ImageFile1', 'ImageFile2', 'ImageFile3', 'ImageFile4', 'ImageFile5', 'ImageFile6'
            )
        );

        return $this->retryPost();
    }

    /**
     * 若發生錯誤重新嘗試，次數預設3次
     * 
     * @param  integer $tryCount 
     * @return object           
     */
    protected function retryPost($tryCount = Yahoo::DEFAULT_RETRY_NUM)
    {
        $response = $this->curler->post($this->url, $this->queryParameters);

        if (
            isset($response->Response->Status) 
            && $response->Response->Status === 'fail' 
            && $tryCount > 0
        ) {
            $tryCount --;

            return $this->retry($tryCount);
        }

        return $this->publicProtected($response);
    }

    protected function getParameterFactory($factoryName)
    {
        $className = '\Woojin\Utility\YahooApi\Factory\\' . $factoryName;
        
        return new $className;
    }

    /**
     * 若發生錯誤重新嘗試，次數預設3次
     * 
     * @param  integer $tryCount 
     * @return object           
     */
    protected function retry($tryCount = Yahoo::DEFAULT_RETRY_NUM)
    {
        $response = $this->curler->get($this->getUrlProcess());

        if (
            isset($response->Response->Status) 
            && $response->Response->Status === 'fail' 
            && $tryCount > 0
        ) {
            $tryCount --;

            return $this->retry($tryCount);
        }

        if (!$response) {
            throw new \Exception('url error!!');
        }

        return $this->publicProtected($response);
    }

    protected function getUrlProcess()
    {
        return $this->url . '?' . $this->preparer->makeQueryString($this->queryParameters)->getQueryString();
    }

    protected function publicProtected(\stdClass $response)
    {
        $jResponse = str_replace(
            array('@Status', '@Count', '@CheckSum', '@Id', '@FileName'), 
            array('Status', 'Count', 'CheckSum', 'Id', 'FileName'),
            json_encode($response)
        );

        return json_decode($jResponse);
    }

    protected function filterDeleteAndOffline($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $product) {
                if (!$product->getYahooId()) {
                    unset($input[$key]);
                }
            }

            if (empty($input)) {
                return false;
            }
        } else {
            if (!$input->getYahooId()) {
                return false;
            }
        }

        return $input;
    }

    public function listSearchId(array $list, $id)
    {
        foreach ($list as $obj) {
            if (isset($obj->Id) && ($obj->Id == $id)) {
                return true;
            }
        }

        return false;
    }

    public function isDeleteExist(\stdClass $response)
    {
        if (!isset($response->Response->FailList->ProductId)) { 
            return false;
        }

        $ids = $response->Response->FailList->ProductId;
            
        if (!isset($ids[0]) || !isset($ids[0]->ErrorCode) || 1380 !== $ids[0]->ErrorCode) {
            return false;
        }

        return true;
    }
}
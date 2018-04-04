<?php

namespace Woojin\Utility\YahooApi\Helper;

use Woojin\Utility\Helper\CurlHelper;
use Woojin\Utility\YahooApi\Yahoo; 

/**
 * 幫Sender 準備要發送的參數, (製作簽章和置入簽章)
 */
class Preparer
{
    protected $apiKey;
    protected $secret;
    protected $timeStamp;
    protected $format;
    protected $queryParamter;
    protected $signature;
    protected $timeDelay;
    protected $queryString;
    protected $curler;

    /**
     * apikey, secret, format 這三個是一開始就定好的, 初始化完成後就不要再廢心思去管了
     * 
     * @param string $apiKey 
     * @param string $secret 
     * @param string $format 
     */
    public function __construct(CurlHelper $curler, $apiKey, $secret, $format = 'json')
    {
        $this
            ->setCurler($curler)
            ->setApiKey($apiKey)
            ->setSecret($secret)
            ->setFormat($format)
        ;

        return $this;
    }

    /**
     * 取得拋給Yahoo的參數
     * 
     * @param  array  $queryParamter
     * @param  array  $ingores        [忽略不加入製作標籤的值]
     * @return array
     */
    public function getParameter(array $queryParamter, array $ingores = array())
    {
        $startTime = microtime();

        // 每次做新的簽章都要使用新的時間戳記
        $this
            ->setTimeStamp($this->getEchoTimeStamp())
            ->setTimeDelay(1)
            ->setSignature($this->makeSignature($this->filter($queryParamter, $ingores)))
        ;

        return array_merge(
            array(
                'ApiKey' => $this->apiKey,
                'TimeStamp' => ($this->timeStamp + $this->timeDelay)
            ), 
            $queryParamter, 
            array(
                'Format' => $this->format,
                'Signature' => $this->signature
        ));
    }

    /**
     * 從 Yahoo 的 server 取得時間戳記物件
     * 
     * @param  boolean $isOrgin [若為真則返回時間物件]
     * @return object/string
     */
    public function getEchoTimeStamp($isOrgin = null)
    {
        $response = $this->curler->get(Yahoo::YAHOO_API_URL . '/v1/echo?Format=json');

        if (!is_object($response)) {
            return $this->getEchoTimeStamp($isOrgin);
        }

        return ($isOrgin) ? $response->Response : $response->Response->TimeStamp;
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * 可參考 http://tw.ews.mall.yahooapis.com/handbook_v2/webservice_guide/ch04s02s05.html
     *
     * 簡單說就是 ApiKey + TimeStamp + QueryString
     *
     * @return string
     */
    protected function makeSignature(array $queryParamter)
    {
        $r = array_merge(
            array(
                'ApiKey' => $this->apiKey,
                'TimeStamp' => ($this->timeStamp + $this->timeDelay)
            ),
            $queryParamter,
            array('Format' => $this->format)
        );

        return $signature = hash_hmac(
            Yahoo::ENCODE_ALG,
            $this->makeQueryString($r)->getQueryString(),
            $this->secret
        );
    }

    /**
     * 將不應該加入標籤材料的參數移除
     * 
     * @param  array  $queryParamter 
     * @param  array  $ingores       
     * @return array  $queryParamter     
     */
    protected function filter(array $queryParamter, array $ingores)
    {
        foreach ($ingores as $ingore) {
            if (array_key_exists($ingore, $queryParamter)) {
                unset($queryParamter[$ingore]);
            }
        };

        return $queryParamter;
    }

    /**
     * 製作Yahoo需要的QueryString
     * 
     * @param  array  $r
     * @return $this   
     */
    public function makeQueryString(array $r)
    {
        $this
            ->setQueryString(null)
            ->iterateContact($r)
            ->wrapQueryString()
        ;

        return $this;
    }

    /**
     * 組成 Query String
     * @param  array  $r
     * @return $this   
     */
    protected function iterateContact(array $r)
    {
        foreach ($r as $key => $val) {
            (is_array($val)) 
            ? $this->iterateContact($val) 
            : $this->contact($this->processRepeatKey($key, $val), $val);
        }  

        return $this;
    }

    /**
     * 若參數名稱包含 '_!' , 視為重複參數處理
     * 
     * @param  string $key
     * @param  string $val
     * @return string     
     */
    protected function processRepeatKey($key, $val)
    {
        return (($pos = strpos($key, '_!')) !== false ? substr($key, 0, $pos) : $key);
    }

    /**
     * 連接字串
     * 
     * @param  string $key 
     * @param  string $val 
     * @return $this     
     */
    protected function contact($key, $val)
    {
        $this->queryString .= $key . '=' . $val . '&';

        return $this;
    }

    /**
     * 取除Query String 的尾部
     * 
     * @return $this
     */
    protected function wrapQueryString()
    {
        $this->queryString = substr($this->queryString, 0, -1);

        return $this;
    }

    public function setCurler(CurlHelper $curler)
    {
        $this->curler = $curler;

        return $this;
    }

    public function setApiKey($str)
    {
        $this->apiKey = $str;

        return $this;
    }

    public function setSecret($str)
    {
        $this->secret = $str;

        return $this;
    }

    public function setFormat($str)
    {
        $this->format = $str;

        return $this;
    }

    public function setTimeStamp($str)
    {
        $this->timeStamp = $str;

        return $this;
    }

    public function setQueryParameter($str)
    {
        $this->queryParameter = $str;

        return $this;
    }

    public function setSignature($str)
    {
        $this->signature = $str;

        return $this;
    }

    public function setQueryString($str)
    {
        $this->queryString = $str;

        return $this;
    }

    public function setTimeDelay($time)
    {
        $this->timeDelay = $time;

        return $this;
    }
}
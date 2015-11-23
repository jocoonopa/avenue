<?php

namespace Woojin\Utility\Helper;

class CurlHelper
{
    /**
     * api_caller 無法支援 yahoo api 的多同名參數, 
     * 只好自己再做一個 cURL 方法
     * 
     * @param  string $url  
     * @return json      
     */
    public function get($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        
        $data = curl_exec($ch);
        
        curl_close($ch);

        return json_decode($data);
    }

    /**
     * 因為 Yahoo 有上傳圖片的 api, 這個一定要用 post, 因此增開此方法
     * 
     * @param  string $url 
     * @param  array  $r   
     * @return json      
     */
    public function post($url, array $r)
    {
        $ch = curl_init();
        $timeout = 5;

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1); // 啟用POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, $r);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        
        $data = curl_exec($ch);
        
        curl_close($ch);

        return json_decode($data);
    }
}
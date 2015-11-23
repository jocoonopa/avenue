<?php

namespace Woojin\Utility\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FacebookResponseHandler
{
    /**
     * 臉書認證回應物件
     * @example
     * {
     *     accessToken: "很長一串亂碼"
     *     expiresIn: 6996
     *     signedRequest: "也是很長一段亂碼"
     *     userID: "10203489456736762"
     *     status: "connected"
     * }
     * 
     * @var object
     */
    protected $authResponse;

    /**
     * FB 連結狀態
     * 
     * @var string
     */
    protected $status;

    /**
     * 臉書使用者資訊
     * @example
     * {
     *     email: 'jocoonopa@hotmail.com'
     *     gender: 'male'
     *     id: "10203489456736762"
     *     last_name: "洪"
     *     link: "https://www.facebook.com/app_scoped_user_id/10203489456736762/"
     *     locale: "zh_TW"
     *     name: "洪小閎"
     *     timezone: 8
     *     updated_time: "2014-02-25T23:41:53+0000"
     *     verified: true
     * }
     * 
     * @var object
     */
    protected $information;

    /**
     * @param json $jAuthResponse
     * @param json $jInformation
     */
    public function __construct($jAuthResponse, $jInformation)
    {
        $authResponse = json_decode($jAuthResponse);
        $this->authResponse = $authResponse->authResponse;
        $this->status = $authResponse->status;

        $this->information = json_decode($jInformation);
    }

    public function getAccessToken()
    {
        return $this->authResponse->accessToken;
    }

    public function getUid()
    {
        return $this->authResponse->userID;
    }

    public function getEmail()
    {
        return $this->information->email;
    }

    public function getName()
    {
        return $this->information->name;
    }

    public function getSex()
    {
        switch($this->information->gender)
        {
            case 'male':
                return '先生';

                break;

            case 'female':
                return '小姐';

                break;

            default:
                return '保密';

                break;
        }
    }

    /**
     * 根據accessToken 和 uid 像 fb 驗證身分
     * 
     * @param  string $accessToken
     * @param  string $uid        
     * @return boolean             
     */
    public function verify()
    {
        $jsonProfile = $this->curl('https://graph.facebook.com/me?access_token=' . $this->authResponse->accessToken);

        $profile = json_decode($jsonProfile);

        return (isset($profile->error)) 
            ? false
            : (($this->getUid() == $profile->id) && ($this->getEmail() === $profile->email) && ($profile->verified == 'true'));
    }

    /**
     * 透過 uid 取得使用者照片連結
     * 
     * @return url
     */
    public function getGraphUrl()
    {
        return 'http://graph.facebook.com/' . $this->getUid() . '/picture';
    }

    // FB 取得使用者生日
    public function getBirthday()
    {
        $graph_url = 'https://graph.facebook.com/me?fields=birthday&access_token=' . $this->authResponse->accessToken;
        $result = json_decode(file_get_contents($graph_url));
        
        return new \DateTime($result->birthday);
    }

    private function curl($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}
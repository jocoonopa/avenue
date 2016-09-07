<?php

namespace Woojin\FrontBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;  
use Woojin\GoodsBundle\Entity\Promotion;
use Woojin\GoodsBundle\Entity\Img;
use Woojin\GoodsBundle\Entity\Desimg;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\Utility\Avenue\Avenue;

class AvenueExtension extends \Twig_Extension
{
    protected $container;
    
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getFilters() 
    {
        return array(
            new \Twig_SimpleFilter('json_decode', array($this, 'jsonDecode')),
            new \Twig_SimpleFilter('trans_level', array($this, 'transLevel')),
            new \Twig_SimpleFilter('str_pad_left', array($this, 'strPadLeft')),
            new \Twig_SimpleFilter('mask_account', array($this, 'maskAccount'))
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getPromotionDiffDays', array($this, 'getPromotionDiffDays')),
            new \Twig_SimpleFunction('getBadgeStyleByCount', array($this, 'getBadgeStyleByCount')), 
            new \Twig_SimpleFunction('isMobile', array($this, 'isMobile')), 
            new \Twig_SimpleFunction('getProductLocate', array($this, 'getProductLocate')),
            new \Twig_SimpleFunction('getBrandList', array($this, 'getBrandList')),
            new \Twig_SimpleFunction('getCacheImage', array($this, 'getCacheImage')), 
            new \Twig_SimpleFunction('getBackCacheImage', array($this, 'getBackCacheImage')),
            new \Twig_SimpleFunction('getPatternGroupList', array($this, 'getPatternGroupList')),
            new \Twig_SimpleFunction('getPortFolioTitle', array($this, 'getPortFolioTitle')),
            new \Twig_SimpleFunction('getCacheDesImage', array($this, 'getCacheDesImage')),
            new \Twig_SimpleFunction('getFBLoginUrl', array($this, 'getFBLoginUrl')),
            new \Twig_SimpleFunction('getGooglePlusParams', array($this, 'getGooglePlusParams')),
            new \Twig_SimpleFunction('getGooglePlusCallBackUrl', array($this, 'getGooglePlusCallBackUrl')) 
        );
    }

    public function getPromotionDiffDays(Promotion $promotion)
    {
        $now = new \DateTime();
        $stopAt = $promotion->getStopAt();
        $interval = $now->diff($stopAt);

        return $interval->format('%a');
    }

    public function getBadgeStyleByCount($count)
    {
        if ($count <= 10) {
            return 'badge-light';
        } else if($count <= 20) {
            return 'badge-green';
        } else if($count <= 40) {
            return 'badge-blue';
        } else if($count <= 70) {
            return 'badge-purple';
        } else if($count <= 110) {
            return 'badge-yellow';
        } else if($count <= 160) {
            return 'badge-orange';
        } 
    }

    public function transLevel(GoodsPassport $product)
    {
        if ($level = $product->getLevel()) {
            switch ($level->getId())
            {
                case Avenue::GL_NEW:
                case Avenue::GL_DEMO:
                    return $level->getName();

                    break;
                default:
                    return '二手';

                    break;
            }
        }

        return '未區分';
    }

    public function getProductLocate(GoodsPassport $product)
    {
        $maps = array(
            'Z' => '中和',
            'Y' => '永和',
            'X' => '新莊',
            'P' => '板橋',
            'L' => '蘆洲',
            'T' => '忠孝',
            '$' => '倉庫'
        );

        $prefix = substr($product->getSn(), 0, 1);

        return array_key_exists($prefix, $maps) ? $maps[$prefix] : null;
    }

    public function jsonDecode($str) 
    {
        return json_decode($str);
    }

    public function strPadLeft($str) 
    {
        return str_pad($str, 10, '0', STR_PAD_LEFT);
    }

    public function getName()
    {
        return 'avenue_extension';
    }

    public function maskAccount($str)
    {
        $chars = str_split($str);
        $newStr = '';

        foreach ($chars as $key => $char) {
            $newStr .= ($key % 2 === 0) ? $char : '*';
        }

        return $newStr;
    }

    public function isMobile()
    {
        return $this->container->get('base_method')->isMobile();
    }

    public function getBrandList()
    {
        return $brandList = array(
            'HERMES'    => Avenue::BRAND_HERMES,
            'CHANEL'    => Avenue::BRAND_CHANEL,
            'LV'        => Avenue::BRAND_LV,
            'GUCCI'     => Avenue::BRAND_GUCCI,
            'PRADA'     => Avenue::BRAND_PARADA,
            'CHLOE'     => Avenue::BRAND_CHLOE,
            'PARIS'     => Avenue::BRAND_PARIS,
            'YSL'       => Avenue::BRAND_YSL,
            'OTHER'     => Avenue::BRAND_OTHER
        );
    }

    public function getPatternGroupList()
    {
        return array(
            '包包' => Avenue::PATTERN_GROUP_BAG,
            '皮夾' => Avenue::PATTERN_GROUP_WALLET,
            '配件' => Avenue::PATTERN_GROUP_ITEM,
            '飾品' => Avenue::PATTERN_GROUP_FIXTURE
        );
    }

    public function getPortFolioTitle($brandId, $patternGroupId)
    {
        return array_search($brandId, $this->getBrandList()) . array_search($patternGroupId, $this->getPatternGroupList());
    }

    public function getCacheImage(Img $img, $width, $height, $isForceOver = false)
    {
        $name = md5($img->getId());

        $prefix = implode(str_split(substr($name, 0, 5)), '/');

        $cachePath = 'cache/image/' . $prefix . '/' . $width .'/' . $height .'/' . $name . '.jpg';

        if (file_exists($cachePath) && !$isForceOver) {
            return '/' . $cachePath;
        }

        /**
         * Gregwar 圖片處理 Service
         * 
         * @var object
         */
        $imageHandling = $this->container->get('image.handling');

        $imageHandling
            // ->open($_SERVER['DOCUMENT_ROOT'] . $img->getPath())
            ->open($img->getPath())
            ->resize($width, $height)
            ->save($cachePath)
        ;

        return '/' . $cachePath;
    }

    public function getCacheDesImage(Desimg $img, $width, $height, $isForceOver = false)
    {
        $name = md5($img->getId());

        $prefix = implode(str_split(substr($name, 0, 5)), '/');

        $cachePath = 'cache/image/des/' . $width .'/' . $height .'/' . $name . '.jpg';

        if (file_exists($cachePath) && !$isForceOver) {
            return '/' . $cachePath;
        }

        /**
         * Gregwar 圖片處理 Service
         * 
         * @var object
         */
        $imageHandling = $this->container->get('image.handling');

        $imageHandling
            // prod->open($_SERVER['DOCUMENT_ROOT'] . $img->getPath())
            ->open($img->getPath())
            ->resize($width, $height)
            ->save($cachePath)
        ;

        return '/' . $cachePath;
    }

    public function getBackCacheImage($path, $width, $height, $isForceOver = false)
    {
        $name = md5($path);

        $prefix = implode(str_split(substr($name, 0, 5)), '/');

        $cachePath = 'cache/image/' . $prefix . '/' . $width .'/' . $height .'/' . $name . '.jpg';

        if (file_exists($cachePath) && !$isForceOver) {
            return '/' . $cachePath;
        }

        /**
         * Gregwar 圖片處理 Service
         * 
         * @var object
         */
        $imageHandling = $this->container->get('image.handling');

        $imageHandling
            ->open($path)
            ->resize($width, $height)
            ->save($cachePath)
        ;

        return '/' . $cachePath;
    }

    public function getFBLoginUrl()
    {
        $fbParam = $this->container->getParameter('fb');

        $fb = new \Facebook\Facebook([
            'app_id' => $fbParam['app_id'],
            'app_secret' => $fbParam['app_secret'],
            'default_graph_version' => $fbParam['api_version'],
        ]);

        $helper = $fb->getRedirectLoginHelper();
        $permissions = ['email']; // optional
        
        return $loginUrl = $helper->getLoginUrl($this->container->get('router')->generate('front_custom_verifyFblogin', array(), true), $permissions);
    }

    public function getGooglePlusParams()
    {
        return $this->container->getParameter('google');
    }

    public function getGooglePlusCallBackUrl()
    {
        return;
    }
}
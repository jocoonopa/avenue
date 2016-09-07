<?php

namespace Woojin\StoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface; 

use Woojin\UserBundle\Entity\User;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\GoodsBundle\Entity\YahooCategory;
use Woojin\Utility\Avenue\Avenue;

class AdminExtension extends \Twig_Extension
{
    protected $container;
    
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getFilters() 
    {
        return array(
            new \Twig_SimpleFilter('has_auth', array($this, 'hasAuth')),
            new \Twig_SimpleFilter('is_own', array($this, 'isOwn')),
            new \Twig_SimpleFilter('is_this_yahoo_category', array($this, 'isThisYahooCategory')),
            new \Twig_SimpleFilter('get_yahoo_form_action', array($this, 'getYahooFormAction')),
            new \Twig_SimpleFilter('locale_week', array($this, 'localeWeek')),
            new \Twig_SimpleFilter('get_hd_name', array($this, 'getHdName'))
        );
    }

    /**
     * !!注意!!!!
     * 
     * 空的也要有回傳陣列，不然會報錯
     */
    public function getFunctions()
    {
        return array();
    }

    public function isOwn($user, GoodsPassport $product)
    {
        return ($user instanceof User) ? ($user->getStore()->getSn() === substr($product->getSn(), 0, 1)) : false;
    }

    /**
     * 判斷該使用者是否具有對應操作權限
     * 
     * @param  Woojin\UserBundle\Entity\User    $user
     * @param  string  $targetName
     * @return boolean            
     */
    public function hasAuth($user, $targetName)
    {
        return ($user instanceof User) ? $user->getRole()->hasAuth($targetName) : false;
    }

    /**
     * 判斷該yahoo分類選項是否適用於該商品
     * 
     * @return boolean
     */
    public function isThisYahooCategory(YahooCategory $yc, GoodsPassport $product)
    {
        return ($yBrand = $yc->getBrand()) && ($pBrand = $product->getBrand()) 
            ? ($yBrand->getId() === $pBrand->getId()) 
            : false;
    }

    public function getYahooFormAction(GoodsPassport $product)
    {
        $router = $this->container->get('router');

        return ($product->getYahooId()) 
                ? $router->generate('admin_yahoo_update', array('id' => $product->getId()))
                : $router->generate('admin_yahoo_create', array('id' => $product->getId()))
            ;
    }

    public function localeWeek($week)
    {

        switch ($week)
        {
            case 'Mon':
                $week = '一';
                break;

            case 'Tue':
                $week = '二';
                break;

            case 'Wed':
                $week = '三';
                break;

            case 'Thu':
                $week = '四';
                break;

            case 'Fri':
                $week = '五';
                break;

            case 'Sat':
                $week = '六';
                break;

            case 'Sun':
                $week = '日';
                break;

            default:
                break;
        }

        return $week;
    }

    public function getHdName($hd)
    {
        switch ($hd)
        {
            case Avenue::HD_NORMAL:
                $hd = '正常';
                break;

            case Avenue::HD_OFFICIAL:
                $hd = '休假';
                break;

            case Avenue::HD_EVENT:
                $hd = '事假';
                break;

            case Avenue::HD_SICK:
                $hd = '病假';
                break;

            case Avenue::HD_YEAR:
                $hd = '年假';
                break;

            case Avenue::HD_GLORY:
                $hd = '榮譽';
                break;

            case Avenue::HD_LOST:
                $hd = '喪假';
                break;

            case Avenue::HD_PREG:
                $hd = '產假';
                break;

            case Avenue::HD_COMPANY:
                $hd = '公假';
                break;

            default:
                break;
        }

        return $hd;
    }

    public function getName()
    {
        return 'admin_extension';
    }
}
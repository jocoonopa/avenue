<?php

namespace Woojin\BaseBundle;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class BaseMethod
{
  protected $context;
  protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em, SecurityContext $context)
  {
    $this->context = $context;
    $this->em = $em;
  }

  public function getSymbol()
  {
    return $this->context->getToken()->getUser()->getStore()->getExchangeRate()->getSymbol();
  }

  public function getRate()
  {
    return $this->context->getToken()->getUser()->getStore()->getExchangeRate()->getRate();
  }

  public function getGoods($id)
  {
    return $this->em
      ->getRepository('WoojinGoodsBundle:GoodsPassport')
      ->find($id);
  }

  public function getGoodsStatus($id)
  {
    return $this->em
      ->getRepository('WoojinGoodsBundle:GoodsStatus')
      ->find($id);
  }

  public function getGoodsLevel($id)
  {
    return $this->em
      ->getRepository('WoojinGoodsBundle:GoodsLevel')
      ->find($id);
  }

  public function getGoodsMt($id)
  {
    return $this->em
      ->getRepository('WoojinGoodsBundle:GoodsMT')
      ->find($id);
  }

  public function getGoodsSource($id)
  {
    return $this->em
      ->getRepository('WoojinGoodsBundle:GoodsSource')
      ->find($id);
  }

  public function getOrders($id)
  {
    return $this->em
      ->getRepository('WoojinOrderBundle:Orders')
      ->find($id);
  }

  public function getOrdersStatus($id)
  {
    return $this->em
      ->getRepository('WoojinOrderBundle:OrdersStatus')
      ->find($id);
  }

  public function getOrdersKind($id)
  {
    return $this->em
      ->getRepository('WoojinOrderBundle:OrdersKind')
      ->find($id);
  }

  public function getPayType($id)
  {
    return $this->em
      ->getRepository('WoojinOrderBundle:PayType')
      ->find($id);
  }

  public function getCustom($id)
  {
    return $this->em
      ->getRepository('WoojinOrderBundle:Custom')
      ->find($id);
  }

  public function getBrandSn($id)
  {
    return $this->em
      ->getRepository('WoojinGoodsBundle:BrandSn')
      ->find($id);
  }

  public function repNeg($n)
  {
    return ($n < 0) ? 0 : $n;
  }

  public function getStart($arr, $flag = 0)
  {
    return $arr[ (0 + $flag) ];
  }

  public function getEnd($arr, $flag = 0)
  {
    return $arr[(count($arr) - 1 - $flag)];
  }

  public function printR($obj)
  {
    echo "<pre>";print_r($obj);echo "</pre>";
    return $obj;
  }

  public function isMobile()
  {
    $regex_match = "/(nokia|iphone|iPad|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
    $regex_match .= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
    $regex_match .= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
    $regex_match .= "symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";   
    $regex_match .= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
    $regex_match .= ")/i";

    return array_key_exists('HTTP_USER_AGENT', $_SERVER) ? preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT'])) : false;
  }
}
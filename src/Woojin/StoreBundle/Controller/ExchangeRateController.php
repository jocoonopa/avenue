<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woojin\StoreBundle\Entity\ExchangeRate;

/**
 * Rating controller.
 *
 * @Route("/exchange_rate")
 */
class ExchangeRateController extends Controller
{
  const STATUS_OK     = 1;
  const STATUS_FAIL   = 0;
  const TYPE_ADD      = 1;
  const TYPE_DELETE   = 2;
  const TYPE_UPDATE   = 3;

  /**
   * 匯率設定首頁
   *
   * @Route("/", name="exchange_rate")
   * @Method("GET")
   * @Template()
   */
  public function indexAction()
  {
    return array();
  }

  /**
   * 修改匯率值
   *
   * @Route("/api/{id}", name="api_update_exchange_rate", options={"expose"=true})
   * @Method("PUT")
   */
  public function apiUpdateAction(Request $request, $id)
  {
    $content = $this->get('request')->getContent();
    $data = ( !empty( $content ) ) ? json_decode( $content, true ) : array();  

    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->beginTransaction();

    try {

      $exchangeRate = $em->getRepository('WoojinStoreBundle:ExchangeRate')->find( $id );
      $exchangeRate->setName( $data['name'] )->setSymbol( $data['symbol'] )->setRate( $data['rate'] );

      $this->get( 'my_meta_record_method' )->recordMeta( '修改' . $data['name'] . '匯率' . $data['rate'] . '' );

      $em->flush();
      $em->getConnection()->commit();

      return new Response(json_encode(array(
        'status'  => self::STATUS_OK, 
        'id'      => $id, 
        'type'    => self::TYPE_UPDATE
      )));

    } catch (Exception $e) {
      $em->getConnection()->rollback();

      throw $e;
    }
  }

  /**
   * 新增匯率幣種
   * 
   * @Route("/api", name="api_add_exchange_rate", options={"expose"=true})
   * @Method("POST")
   */
  public function apiAddAction()
  {

  }

  /**
   * 刪除匯率幣種
   * 
   * @Route("/api/{id}", name="api_delete_exchange_rate", options={"expose"=true})
   * @Method("DELETE")
   */
  public function apiDeleteAction()
  {

  }

  /**
   * 取得所有匯率幣種
   * 
   * @Route("/api/{id}", name="api_get_exchange_rate", options={"expose"=true})
   * @Method("GET")
   */
  public function apiGetAction()
  {

  }

  /**
   * 取得所有匯率幣種
   * 
   * @Route("/api", name="api_query_exchange_rate", options={"expose"=true})
   * @Method("GET")
   */
  public function apiQueryAction()
  {
    $exchangeRates = $this->getDoctrine()->getRepository('WoojinStoreBundle:ExchangeRate')->findAll();
    
    $rRate = array();

    foreach ($exchangeRates as $key => $eachRate) {
      $rTmp             = array();
      $rTmp['id']       = $eachRate->getId();
      $rTmp['name']     = urlencode( $eachRate->getName() );
      $rTmp['symbol']   = urlencode( $eachRate->getSymbol() );
      $rTmp['rate']     = $eachRate->getRate();

      array_push( $rRate, $rTmp);
    }

    return new Response( urldecode( json_encode($rRate) ) );
  }
}

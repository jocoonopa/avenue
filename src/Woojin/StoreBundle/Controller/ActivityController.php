<?php

namespace Woojin\StoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Woojin\GoodsBundle\Entity\BsoMoveLog;
use Woojin\StoreBundle\Entity\Activity;
use Woojin\StoreBundle\Form\ActivityType;
use Woojin\Utility\Avenue\Avenue;

/**
 * Activity controller.
 *
 * @Route("/activity")
 */
class ActivityController extends Controller
{
    /**
    * SPA template
    *
    * @Route("/", name="activity")
    * @Method("GET")
    * @Template()
    */
    public function indexAction()
    {
        return array();
    }

    /**
    * List template
    * 
    * @Route("/template/list", name="activity_template_list", options={"expose"=true})
    * @Method("GET")
    * @Template()
    */
    public function templateListAction()
    {
        return array();
    }

    /**
    * Detail template
    *
    * @Route("/template/detail", name="activity_template_detail", options={"expose"=true})
    * @Method("GET")
    * @Template()
    */
    public function templateDetailAction()
    {
        return array();
    }

    /**
    * Lists all Visible Activity entities.
    *
    * @Route("/api", name="actlist", options={"expose"=true})
    * @Method("GET")
    */
    public function apiActlistAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        return $this->returnActivityList($em, $em->getRepository('WoojinStoreBundle:Activity')->findVisible());
    }

    /**
    * Lists all Hidden Activity entities.
    *
    * @Route("/api/fetchhidden", name="fetch_hidden_actlist", options={"expose"=true})
    * @Method("GET")
    */
    public function apiFetchHiddenActlistAction()
    {
        $em = $this->getDoctrine()->getManager();
        
        return $this->returnActivityList($em, $em->getRepository('WoojinStoreBundle:Activity')->findHidden());
    }

    protected function returnActivityList($em, $oActivitys)
    {
        $rActivitys   = array();

        foreach ($oActivitys as $key => $eachActivity) {
            $rTmp                  = array();
            $rTmp['id']            = $eachActivity->getId();
            $rTmp['name']          = urlencode($eachActivity->getName());
            $rTmp['is_hidden']     = (int) $eachActivity->getIsHidden();
            $rTmp['description']   = urlencode( str_replace( "\n", "<br>", $eachActivity->getDescription()) );
            $rTmp['startAt']       = $eachActivity->getStartAt()->format('Y-m-d');
            $rTmp['endAt']         = $eachActivity->getEndAt()->format('Y-m-d');

            $qb = $em->createQueryBuilder();
            $products = $qb->select('g')
                ->from('WoojinGoodsBundle:GoodsPassport', 'g')
                ->where(
                    $qb->expr()->eq('g.activity', $rTmp['id']),
                    $qb->expr()->in('g.status', array(
                        Avenue::GS_ONSALE, Avenue::GS_ACTIVITY, Avenue::GS_SOLDOUT
                    ))
                )
                ->getQuery()
                ->getResult()
            ;

            $rTmp['count'] = count($products);


            array_push($rActivitys, $rTmp);
        }

        $json = json_encode($rActivitys);

        return new Response(urldecode($json));
    }

    /**
     * 使活動可見
     * 
     * @Route("/api/makevisible/{id}", name="api_makevisible_actlist", options={"expose"=true}, requirements={"id": "\d+"})
     * @Method("PUT")
     */
    public function apiMakeVisibleActAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $activity = $em->getRepository('WoojinStoreBundle:Activity')->find($id);

        $activity->setIsHidden(false);
        $em->persist($activity);
        $em->flush();

        return new Response(0);
    }

    /**
    * Lists all Activity entities.
    *
    * @Route("/api/hide/{id}", name="api_hide_actlist", options={"expose"=true}, requirements={"id": "\d+"})
    * @Method("PUT")
    */
    public function apiHideActAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $activity = $em->getRepository('WoojinStoreBundle:Activity')->find($id);

        $activity->setIsHidden(true);
        $em->persist($activity);
        $em->flush();

        return new Response( 
            json_encode( 
              array('id' => $activity->getId(), 'name' => $activity->getName()) 
            ) 
        );
    }

    /**
    * Show an Activity entity.
    *
    * @Route("/api/{id}", name="api_show_activity", options={"expose"=true}, requirements={"id": "\d+"})
    * @Method("GET")
    */
    public function apiShowAction($id)
    {
        $em                         = $this->getDoctrine()->getManager();
        $oActivity                  = $em->getRepository('WoojinStoreBundle:Activity')->find($id);
        $activity['id']             = urlencode($oActivity->getId());
        $activity['name']           = urlencode($oActivity->getName());
        $activity['description']    = urlencode( str_replace("\n","<br>", $oActivity->getDescription()));
        $activity['startAt']        = $oActivity->getStartAt()->format('Y-m-d');
        $activity['endAt']          = $oActivity->getEndAt()->format('Y-m-d');

        $json = json_encode($activity);
        return new Response(urldecode($json));
    }

  /**
   * Create an Activity entity.
   *
   * @Route("/api", name="api_create_activity", options={"expose"=true}, requirements={"id": "\d+"})
   * @Method("POST")
   */
  public function apiCreateAction(Request $request)
  {
	$oActivity  = new Activity();
	$content    = $this->get('request')->getContent();
	$data       = ( !empty( $content ) ) ? json_decode( $content, true ) : array();
	
	$em = $this->getDoctrine()->getManager();
	$em->getConnection()->beginTransaction();
	
	try {

	  $oActivity
		->setName($data['name'])
		->setStartAt( new \DateTime( $data['startAt'] ) )
		->setEndAt( new \DateTime( $data['endAt'] ) )
		->setDescription( $data['description'] )
        ->setIsHidden(false)
	  ;
	  
	  $em->persist( $oActivity );
	  $em->flush();
	  $em->getConnection()->commit();

	  //操作記錄
	  $sMsg = '新增活動' . $content;
	  $this->get( 'my_meta_record_method' )->recordMeta( $sMsg );
	  
	  return new Response( 
		json_encode( 
		  array( 'id' => $oActivity->getId() ) 
		) 
	  );

	} catch ( Exception $e ) {

	  $em->getConnection()->rollback();

	  throw $e;
	}
  }

  /**
   * Delete an Activity entity.
   *
   * @Route("/api/{id}", name="api_delete_activity", options={"expose"=true}, requirements={"id": "\d+"})
   * @Method("DELETE")
   */
  public function apiDeleteAction($id)
  {
	$em = $this->getDoctrine()->getManager();
	$em->getConnection()->beginTransaction();
	
	try {

	  $oActivity = $this
		->getDoctrine()
		->getRepository('WoojinStoreBundle:Activity')
		->find( $id )
	  ;
	  
	  $em->remove( $oActivity );
	  $em->flush();
	  $em->getConnection()->commit();

	  //操作記錄
	  $sMsg = '刪除活動' . $oActivity->getName();
	  $this->get('my_meta_record_method')->recordMeta( $sMsg );
	  
	  return new Response( 
		json_encode( 
		  array('name' => $oActivity->getName()) 
		) 
	  );

	} catch (Exception $e) {

	  $em->getConnection()->rollback();

	  throw $e;
	}
  }

  /**
   * Update an Activity entity.
   *
   * @Route("/api/{id}", name="api_update_activity", options={"expose"=true}, requirements={"id": "\d+"})
   * @Method("PUT")
   */
  public function apiUpdateAction(Request $request, $id)
  {
	$content  = $this->get('request')->getContent();
	$data     = ( !empty( $content ) ) ? json_decode( $content, true ) : array();  
	
	$em = $this->getDoctrine()->getManager();
	$em->getConnection()->beginTransaction();
	
	try {

	  $oActivity = $this->getDoctrine()->getRepository('WoojinStoreBundle:Activity')->find($data['id']);

	  $org                  = array();
	  $org['Name']          = $oActivity->getName();
	  $org['StartAt']       = $oActivity->getStartAt()->format('Y-m-d');
	  $org['EndAt']         = $oActivity->getEndAt()->format('Y-m-d');
	  $org['Description']   = $oActivity->getDescription();
	  
	  $oActivity
		->setName( $data['name'] )
		->setStartAt( new \DateTime( $data['startAt'] ) )
		->setEndAt( new \DateTime( $data['endAt'] ) )
		->setDescription( $data['description'] )
	  ;

	  $em->flush();
	  $em->getConnection()->commit();

	  //操作記錄
	  $sMsg = json_encode($org) . '更新為' . $content ;
	  $this->get('my_meta_record_method')->recordMeta($sMsg);
	  
	  return new Response( 
		json_encode( 
		  array('id' => $oActivity->getId()) 
		) 
	  );

	} catch (Exception $e) {

	  $em->getConnection()->rollback();

	  throw $e;
	}
  }

    /**
    * Bind Goods Entity with the Activity entity.
    *
    * @Route("/api/punch/in/{id}", name="api_punch_in_update_activity", options={"expose"=true}, requirements={"id": "\d+"})
    * @Method("PUT")
    */
    public function apiUpdatePunchInAction(Request $request, $id)
    {
        /**
         * The Current User
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $returnArray = array();
        $returnArray['success'] = array();
        $returnArray['fail'] = array();
        $rSn = array();
        $content = $this->get('request')->getContent();
        $data = ( !empty( $content ) ) ? json_decode( $content, true) : array();

        // 組成 rSn, 要拿來當後面 where 其中一個條件 
        foreach ($data as $eachData) {
            if (!isset($eachData['sn'])) {
                continue;
            }
            array_push($rSn, strtoupper($eachData['sn']));
        }

        if (empty($rSn)) {
            return new Response('');
        }

        // 修正重複刷件
        $rSn = array_unique($rSn);

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            // 傳入資料過濾 1.上架 2. 字首為本店代碼 3.傳入之rSn
            $qb = $em->createQueryBuilder();
            
            $oRes = $qb
            ->select('gd')
            ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
            ->where( 
                $qb->expr()->andX(
                    $qb->expr()->eq('gd.status', Avenue::GS_ONSALE),
                    // $qb->expr()->eq( 
                    //   $qb->expr()->substring('gd.sn', 1, 1), 
                    //            $qb->expr()->literal($this->get('security.token_storage')->getToken()->getUser()->getStore()->getSn())
                    // ),
                    $qb->expr()->in('gd.sn', $rSn)
                )
            )
            ->groupBy('gd.id')
    	    ->getQuery();

            // 過濾後資料為 rGoods
            $rGoods = $oRes->getResult(); 

            $oGoodsStatus = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ACTIVITY);

            // 取得活動實體 by activity_id , 取得商品狀態實體
            $oActivity = $em->find('WoojinStoreBundle:Activity', $id);

            // 更新所有 rGoods 活動id 為傳入之 
            foreach ($rGoods as $oGoods) {
                $oGoods
                    ->setActivity($oActivity)
                    ->setStatus($oGoodsStatus)
                ;

                // 成功刷入則將其從 rSn 移除，rSn 最後存在的元素表示為刷件失敗之元素，回傳當做錯誤訊息
                unset($rSn[array_search($oGoods->getSn(), $rSn)]);

                // 將成功刷入之產編存入刷件成功陣列
                array_push($returnArray['success'], array('sn' => $oGoods->getSn()));
            }

            // 剩餘的 rSn 表示刷件失敗的產編們，存入刷件失敗陣列
            // 同時 reindex 陣列否則 js 會把陣列誤轉為物件
            $returnArray['fail'] = array_values($rSn);

            $em->flush();
            $em->getConnection()->commit();

            foreach ($rGoods as $oGoods) {
                $log = new BsoMoveLog;
                $log
                    ->setCreater($user)
                    ->setProduct($oGoods)
                    ->setAction('刷入活動' . $oActivity->getName())
                ;
                $em->persist($log);
                $em->flush();
            }

            //操作記錄
            $jReturn = json_encode( $returnArray, true );
            $sMsg = '刷入' . $oActivity->getName() . ':' . $jReturn;
            $this->get('my_meta_record_method')->recordMeta($sMsg);

            // 回傳 rGoods 之 sn 之 json 字串
            return new Response($jReturn);
        } catch (Exception $e) {
            $em->getConnection()->rollback();

            throw $e;
        }
    }

    /**
     * Unbind Goods Entity with the Activity entity
     *
     * @Route("/api/punch/out/{id}", name="api_punch_out_update_activity", options={"expose"=true})
     * @Method("PUT")
     */
    public function apiUpdatePunchOutAction(Request $request, $id)
    {
    	$returnArray              = array();
    	$returnArray['success']   = array();
    	$returnArray['fail']      = array();
    	$rSn                      = array();
    	$content                  = $this->get('request')->getContent();
    	$data                     = (!empty($content)) ? json_decode($content, true) : array();
        /**
         * The Current User
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        // 組成 rSn, 要拿來當後面 where 其中一個條件 
        foreach ($data as $eachData) {
            if (!isset($eachData['sn'])) {
                continue;
            }

            array_push($rSn, strtoupper($eachData['sn']));
        }

        // 修正重複刷件
        $rSn = array_unique($rSn);

        $em = $this->getDoctrine()->getManager();

        $em->getConnection()->beginTransaction();

        try {
            // 傳入資料過濾 1.狀態:活動 2. 字首為本店代碼 3.傳入之rSn
            $qb = $em->createQueryBuilder();
            
            $oRes = $qb
                ->select('gd')
                ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
                ->where( 
                    $qb->expr()->andX(
                            $qb->expr()->eq('gd.status', Avenue::GS_ACTIVITY),
                            $qb->expr()->eq( 
                                $qb->expr()->substring('gd.sn', 1, 1), 
                                $qb->expr()->literal($this->get('security.token_storage')->getToken()->getUser()->getStore()->getSn()
                            )
                        ),
                        $qb->expr()->in('gd.sn', $rSn),
                        $qb->expr()->eq('gd.activity', $id)
                    )
                )
                ->groupBy('gd.id')
                ->getQuery()
            ;

            //取得活動實體
            $oActivity = $em->find('WoojinStoreBundle:Activity', $id);

            // 過濾後資料為 rGoods
            $rGoods = $oRes->getResult();

            $oGoodsStatus = $em->find('WoojinGoodsBundle:GoodsStatus', Avenue::GS_ONSALE);

            // 更新所有 rGoods 活動id 為傳入之 
            foreach ($rGoods as $oGoods) {
                $oGoods
                    ->setActivity(null)
                    ->setStatus($oGoodsStatus)
                ;

                // 成功刷入則將其從 rSn 移除，rSn 最後存在的元素表示為刷件失敗之元素，回傳當做錯誤訊息
                unset($rSn[array_search($oGoods->getSn(), $rSn)]);

                // 將成功刷入之產編存入刷件成功陣列
                array_push($returnArray['success'], array('sn' => $oGoods->getSn()));
            }

            // 剩餘的 rSn 表示刷件失敗的產編們，存入刷件失敗陣列
            // 同時 reindex 陣列否則 js 會把陣列誤轉為物件
            $returnArray['fail'] = array_values($rSn); 

            $em->flush();
            $em->getConnection()->commit();

            foreach ($rGoods as $oGoods) {
                $log = new BsoMoveLog;
                $log
                    ->setCreater($user)
                    ->setProduct($oGoods)
                    ->setAction('刷出活動' . $oActivity->getName())
                ;
                $em->persist($log);
            }

            //操作記錄
            $jReturn = json_encode($returnArray, true);
            $sMsg = '從活動' . $oActivity->getName() . '刷出:' . $jReturn;
            $this->get('my_meta_record_method')->recordMeta($sMsg);

            // 回傳 rGoods 之 sn 之 json 字串
            return new Response($jReturn);
        } catch (Exception $e) {
            $em->getConnection()->rollback();

            throw $e;
        }
    }

    /**
    * Get All Activity Goods 
    * 
    * @Route("/api/{id}/goods", name="api_goods_in_activity", options={"expose"=true})
    * @Method("GET")
    */
    public function apiShowActivityGoodsAction(Request $request, $id)
    {
        $rData    = array();
        $sellNum  = 0;

        $em = $this->getDoctrine()->getManager();
        // $rGoods = $em
        //   ->getRepository('WoojinGoodsBundle:GoodsPassport')
        //   ->findBy( array('activity' => $id ), array('sn' => 'ASC') );

        $qb = $em->createQueryBuilder();
        $products = $qb->select(array('g', 'gs', 'gb'))
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->join('g.status', 'gs')
            ->join('g.brand', 'gb')
            ->where(
                $qb->expr()->eq('g.activity', $id),
                $qb->expr()->in('g.status', array(
                    Avenue::GS_ONSALE, Avenue::GS_ACTIVITY, Avenue::GS_SOLDOUT
                ))
            )
            ->getQuery()
            ->getResult()
        ;

        foreach ($products as $key => $oGoods) {
          $rTmp             = array();
          $rTmp['id']       = $oGoods->getId();
          $rTmp['sn']       = $oGoods->getSn();
          $rTmp['name']     = '';//$oGoods->getName();
          $rTmp['price']    = $oGoods->getPrice();
          $rTmp['status']   = $oGoods->getStatus()->getName();
          $rTmp['brand']    = $oGoods->getBrand()->getName();

          array_push($rData, $rTmp);

          if ($rData[$key]['status'] == '售出') {
        	$sellNum ++;
          }
        }

        // 存入售出件數
        $rData[(count($rData)-1)]['count'] = $sellNum;

        return new Response(json_encode($rData));
    }
}

<?php

namespace Woojin\OrderBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Woojin\OrderBundle\Entity\Custom;
use Woojin\Utility\Avenue\Avenue;

/**
 * Custom controller.
 * 0. 先檢查該action 有無存在必要 v
 * 1. 先改controller內容 v 以及view的名稱以及位置
 * 2. 再改路徑
 * 3. 再改路徑名稱
 *
 * @Route("/custom")
 */
class CustomController extends Controller
{
    /**
     * @Route("/temp", name="order_custom_temp")
     * @Template("WoojinOrderBundle:Custom:temp.html.twig")
     */
    public function tempAction()
    {
        return array('_token' => $this->get('form.csrf_provider')->generateCsrfToken('unknown'));
    }

    /**
     * @Route("/update", name="admin_custom_update", options={"expose"=true})
     * @Template("WoojinOrderBundle:Custom/partial:updateRes.html.twig")
     * @Method("POST")
     */
    public function updateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
            
        $custom = $em->find('WoojinOrderBundle:Custom', $request->request->get('nCustomId'));
        $custom
            ->setName($request->request->get('sCustomName'))
            ->setSex($request->request->get('sCustomSex'))
            ->setMobil($request->request->get('sCustomMobil'))
            ->setEmail($request->request->get('sCustomEmail'))
            ->setAddress($request->request->get('sCustomAddress'))
            ->setBirthday(new \DateTime($request->request->get('sCustomBirthday')))
            ->setMemo($request->request->get('sCustomMemo'))
        ;

        $em->persist($custom);
        $em->flush();

        return array('custom' => $custom);
    }

    /**
     * @Route("", name="admin_custom_create", options={"expose"=true})
     * @Template("WoojinOrderBundle:Custom:res.html.twig")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {  
        $user = $this->get('security.context')->getToken()->getUser();
            
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $qb = $em->createQueryBuilder();
        $custom = $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.mobil', $request->request->get('custom_mobil')),
                    $qb->expr()->eq('c.store', $user->getStore()->getId())
                )
            )
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($custom) {
            throw new \Exception('客戶已經存在!');
        }
            
        try{
            $custom = new Custom();
            $custom
                ->setStore($user->getStore())
                ->setName($request->request->get('custom_name'))
                ->setSex($request->request->get('custom_sex', '保密'))
                ->setMobil($request->request->get('custom_mobil'))
                ->setEmail($request->request->get('custom_email', 'avenueDefault@gmail.com'))
                ->setAddress($request->request->get('custom_address', 'avenue2003預設地址'))
                ->setMemo($request->request->get('custom_memo'))
                ->setCreatetime(new \DateTime())
            ;

            if ($birthday = $request->request->get('custom_birthday')) {
                $custom->setBirthday(new \DateTime($birthday));
            }          

            $em->persist($custom);
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e){
            $em->getConnection()->rollback();

            throw $e;
        }    

        return array('rCustom' => array($custom), 'nCount' => 1, 'nNowPage' => 1);
    }

    /**
     * @Route("/{id}", name="admin_custom_delete", options={"expose"=true})
     * @ParamConverter("custom", class="WoojinOrderBundle:Custom")
     * @Method("DELETE")
     */
    public function destroyAction(Custom $custom) 
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($custom);
        $em->flush();

        return new JsonResponse(array('status' => Avenue::IS_SUCCESS));
    }

    /**
     * @Route("/editform", name="orders_custom_edit_form", options={"expose"=true})
     * @Template("WoojinOrderBundle:Custom:editForm.html.twig")
     * @Method("POST")
     */
    public function editFormAction(Request $request)
    {
        return array(
            'custom' => $this->getDoctrine()->getRepository('WoojinOrderBundle:Custom')->find($request->request->get('nCustomId', Avenue::CUS_NONE)), 
            '_token' => $this->get('form.csrf_provider')->generateCsrfToken('unknown') 
        );
    }

    /**
     * @Route("/checkexist", name="admin_custom_checkExist", options={"expose"=true})
     * @Template("WoojinOrderBundle:Custom/partial:checkExist.html.twig")
     * @Method("POST")
     */
    public function checkExistAction(Request $request)
    { 
        foreach ($request->request->keys() as $key) {
            $val = $request->request->get($key);
            $key = $key;
        }

        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        
        $customs = $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq(
                        'c.' . str_replace('custom_', '', $key), 
                        $qb->expr()->literal($val)
                    ),
                    $qb->expr()->eq('c.store', $user->getStore()->getId())
                )
            )
            ->getQuery()
            ->getResult()
        ;

        return array('rCustom' => $customs);
    }

    /**
     * @Route("/searchbyname", name="admin_custom_searchByName", options={"expose"=true})
     * @Method("GET")
     */
    public function searchByNameAction(Request $request)
    {   
        $user = $this->get('security.context')->getToken()->getUser();

        $names = array();

        $em = $this->getDoctrine()->getManager();
        
        $qb = $em->createQueryBuilder();
        
        $customs = $qb->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->like(
                        'c.name', 
                        $qb->expr()->literal('%' . $request->query->get('term') . '%')
                    ),
                    $qb->expr()->eq('c.store', $user->getStore()->getId())
                )   
            )
            ->setFirstResult(Avenue::START_FROM)
            ->setMaxResults(Avenue::MAX_RES)
            ->getQuery()
            ->getResult()
        ;

        foreach($customs as $custom) {
            $names[] = $custom->getName();
        }   

        return new JsonResponse($names);
    }

    /**
     * @Route("/searchbymobil", name="admin_custom_searchByMobil", options={"expose"=true})
     * @Method("GET")
     */
    public function searchByMobilAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $mobils = array();

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        
        $customs = $qb->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->like(
                        'c.mobil', 
                        $qb->expr()->literal('%' . $request->query->get('term') . '%')
                    ),
                    $qb->expr()->eq('c.store', $user->getStore()->getId())
                )   
            )
            ->getQuery()
            ->getResult()
        ;

        foreach ($customs as $custom) {
            $mobils[] = $custom->getMobil();
        }

        return new JsonResponse($mobils);
    }

    /**
     * @Route("/searchnamebyemail", name="admin_custom_searchName_byEmail", options={"expose"=true})
     * @Method("GET")
     */
    public function searchByEmailAction(Request $request)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        
        $emails = array();

        $qb = $em->createQueryBuilder();
        
        $customs = $qb->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->like(
                    'c.email', 
                    $qb->expr()->literal('%' . $request->query->get('term') . '%')
                )
            )
            ->andWhere($qb->expr()->eq('c.store', $user->getStore()->getId()))
            ->getQuery()
            ->getResult()
        ;

        foreach($customs as $custom) {
            $emails[] = $custom->getCustomEmail();
        }

        return new JsonResponse($emails);
    }

    /**
     * 這段好悲劇....寫成這鬼樣超難改，已哭T_T
     * 
     * @Route("/fetch", name="admin_custom_fetch", options={"expose"=true})
     * @Template("WoojinOrderBundle:Custom:res.html.twig")
     * @Method("POST")
     */
    public function fetchAction(Request $request)
    {    
        $user = $this->get('security.context')->getToken()->getUser();
         
        $em = $this->getDoctrine()->getManager();
        
        $qb = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->eq('c.store', $user->getStore()->getId())
            )
        ;

        foreach ($request->request->all() as $key => $eachCon) {
            switch ($key) {
                case 'customNameSearch':
                    $qb->andWhere($qb->expr()->in('c.name', $eachCon));
                    
                    break;

                case 'monthSearch':
                    $dql = '';

                    foreach ($eachCon as $key_ => $cost_min) {
                        $rArr = $request->request->get($key);
                        $month = $rArr[$key_];
                        if ($month < 10)
                            $month = str_pad($month, 2, 0, STR_PAD_LEFT);
                        $dql .= '(SUBSTRING(c.birthday, 6, 2) =\'' . $month . '\') OR ';
                    }

                    $dql = substr($dql, 0, -3);
                    $qb->andWhere($dql);
                    
                    break;

                case 'birthdaySearchMin':
                    $dql = '';

                    foreach ($eachCon as $key_ => $cost_min) {
                        $rArr = $request->request->get($key);
                        $date = $rArr[$key_];
                        $dql .= '(c.birthday <=' . $date . ' AND c.birthday >='. $date . ') OR ';
                    }

                    $dql = substr($dql, 0, -3);
                    $qb->andWhere($dql);
                    
                    break;

                case 'custom_mobil':
                    $qb->andWhere($qb->expr()->in('c.mobil', $eachCon));
                    
                    break;

                case 'mobileSearch':
                    $qb->andWhere($qb->expr()->in('c.mobil', $eachCon));
                    
                    break;
            }
        }

        $nNowPage = $request->request->get('page', 1);
        $nNowPage = ($nNowPage < 1) ? 1: $nNowPage;
        
        $qbCount = $qb; 
        $nCount = count($qbCount->getQuery()->getResult());

        $qb
            ->orderBy('c.id')
            ->setMaxResults(Avenue::PER_PAGE)
            ->setFirstResult((($nNowPage - 1) * Avenue::PER_PAGE))
        ;

        $rCustom = $qb->getQuery()->getResult();

        return array(
            'rCustom'   => $rCustom,
            'nCount'    => $nCount,
            'nNowPage'  => $nNowPage
        );
    }
}

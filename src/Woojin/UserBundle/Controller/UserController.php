<?php

namespace Woojin\UserBundle\Controller;

use Woojin\UserBundle\Entity\User;
use Woojin\UserBundle\Entity\UserHabit;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UserController extends Controller
{
	const CHMOD               = 5;
	const IS_ACTIVE           = 1;
	const IS_OFF              = 0;
	const ROLE_STAFF          = 1;
	const ROLE_OLD_STAFF      = 4;
	const ROLE_SPECIAL_STAFF  = 5;
	const STORE_DEPOT         = 6;

	/**
	 * @Route("/{id}/edit", requirements={"id"="\d+"}, name="admin_user_edit")
	 * @ParamConverter("user", class="WoojinUserBundle:User")
	 * @Template()
	 * @Method("GET")
	 */
	public function editAction(User $user)
	{	
		$em = $this->getDoctrine()->getManager();

		return array(
			'user' => $user,
			'roles' => $em->getRepository('WoojinUserBundle:Role')->findAll(),
			'stores' => $em->getRepository('WoojinStoreBundle:Store')->findAll()
		);
	}

	/**
	 * @Route("/{id}/edit", requirements={"id"="\d+"}, name="admin_user_update")
	 * @ParamConverter("user", class="WoojinUserBundle:User")
	 * @Method("PUT")
	 */
	public function updateAction(Request $request, User $user)
	{
		$em = $this->getDoctrine()->getManager();

		$user
			->setStore($em->find('WoojinStoreBundle:Store', $request->request->get('store')))
			->setRole($em->find('WoojinUserBundle:Role', $request->request->get('role')))
			->setUsername($request->request->get('username', $user->getUsername()))
			->setEmail($request->request->get('email', $user->getEmail()))
			->setMobil($request->request->get('mobil', $user->getMobil()))
			->setIsActive((int) $request->request->get('is_active') === 1)
		;

		$em->persist($user);
		$em->flush();

		$session = $this->get('session');
        $session->getFlashBag()->add('success', $user->getUsername() . '修改完成');

        return $this->redirect($this->generateUrl('admin_role_index'));
	}
	
	/**
	* @Route("", name="user_base", options={"expose"=true})
	* @Template("WoojinUserBundle:User:user.html.twig")
	*/
	public function indexAction()
	{	  	
	  	$em = $this->getDoctrine()->getManager();

	  	$user = $this->get('security.context')->getToken()->getUser();
		
		$store = $user->getStore();

		$users = $this->getDoctrine()->getRepository('WoojinUserBundle:User')->findBy(array('store' => $store->getId()));

	  	return array('users' => $users);
	}

	/**
	* @Route("/ajax/add", name="user_ajax_add", options={"expose"=true})
	* @Template("WoojinUserBundle:Frag:_user.tbody.html.twig")
	*/
	public function userAjaxAddAction(Request $request)
	{		
		$factory = $this->get('security.encoder_factory');
		
		$oUser = new User();
		
		$rUser = array();
		
		$oStore = $this->get('security.context')->getToken()->getUser()->getStore();
		
		$nRoleId = ($oStore->getId() == self::STORE_DEPOT)? self::ROLE_SPECIAL_STAFF : self::ROLE_STAFF;
		
		$em = $this->getDoctrine()->getManager();
		
		$role = $em->find('WoojinUserBundle:Role', $nRoleId);
   
		$encoder = $factory->getEncoder($oUser);
		
		$password = $encoder->encodePassword($request->request->get('sUserPassword'), $oUser->getSalt());
		$oUser
			->setUsername($request->request->get('sUsername'))
			->setEmail($request->request->get('sEmail'))
			->setMobil($request->request->get('sMobil'))
			->setCreatetime(date('Y-m-d H:i:s'))
			->setStore($oStore)
			->setRoles($role)
			->setChmod(self::CHMOD)
			->setIsActive(self::IS_ACTIVE)
			->setPassword($password)
		;
		
		$em->persist($oUser);
		$em->flush();

		$sMsg = '新增使用者:['.$request->request->get('sUsername').']';
		$sMsg.= '['.$request->request->get('sMobil').']';
		$sMsg.= '['.$request->request->get('sEmail').']';

		$this->get('my_meta_record_method')->recordMeta($sMsg);
		
		array_push($rUser, $oUser);
		
		return array('rUser' => $rUser);
	}

	/**
	* @Route("/ajax/edit", name="user_ajax_edit", options={"expose"=true})
	*/
	public function userAjaxEditAction(Request $request)
	{			
		$em = $this->getDoctrine()->getManager();

		$oUser = $em->find('WoojinUserBundle:User', $request->request->get('nId'));
		
	  	$em->getConnection()->beginTransaction();

	  	try{
			if ($request->request->get('sUserName') != '')
				$oUser->setUsername(trim($request->request->get('sUserName')));
			
			if ($request->request->get('sEmail') != '')
				$oUser->setEmail(trim($request->request->get('sEmail')));
			
			if ($request->request->get('sMobil') != '')
				$oUser->setMobil(trim($request->request->get('sMobil')));
			
			if ($request->request->get('sPassword') != '') {
				$factory = $this->get('security.encoder_factory');
				
				$encoder = $factory->getEncoder($oUser);
				
				$password = $encoder->encodePassword(
					$request->request->get('sPassword'), 
					$oUser->getSalt()
				);

				$oUser->setPassword($password);
			}

			$em->persist($oUser);
			$em->flush();
			$em->getConnection()->commit();

		} catch (Exception $e) {
			$em->getConnection()->rollback();

			return new Response('error');
		}    

		return new Response('ok');
	}

	/**
	* @Route("/ajax/active", name="user_ajax_active", options={"expose"=true})
	*/
	public function userAjaxActiveAction(Request $request)
	{
		if ($request->getMethod() != 'POST')
			return new Response('error');	

		if ($request->request->get('sActive') != 'do')
			return new Response('');

		if ($request->request->get('nId') == '')
			return new Response('user does not exists');

		$oUser = $this->getDoctrine()
			->getRepository('WoojinUserBundle:User')
			->find( $request->request->get('nId') );

		$nActive 	= $oUser->getIsActive() == self::IS_ACTIVE ? self::IS_OFF : self::IS_ACTIVE;
		$sStoptime 	= $nActive == self::IS_OFF ? date('Y-m-d H:i:s') : null;

		$oUser->setIsActive($nActive);
		$oUser->setStoptime($sStoptime);
			
		$em = $this->getDoctrine()->getManager();
		$em->persist($oUser);
		$em->flush();

		$sTheAct = $nActive == self::IS_OFF ? '停權': '開通';
		$sMsg = $sTheAct . ':[' . $oUser->getUsername() . '][' . $oUser->getMobil() . ']';
		$sMsg.= '[' . $oUser->getEmail() . ']';
		$oMetaRecord = $this->get('my_meta_record_method');
	  	$oMetaRecord->recordMeta($sMsg);

		return new Response($sStoptime);
	}

    /**
	 * @Route("/userHistory", name="user_userHistory", options={"expose"=true})
	 * @Template("WoojinUserBundle:User:user.record.history.html.twig")
	 */
	public function userHistoryAction(Request $request)
	{
		$oUser = $this->get('security.context')->getToken()->getUser();

    	$nStoreId = $oUser->getId();

    	$rUsers = $this->getDoctrine()->getRepository('WoojinUserBundle:User')->findBy(array('store_id' => $nStoreId));
        
        return array('rUsers' => $rUsers);
	}

	/**
	 * @Route("/ajax/get/recordHistory", name="user_ajax_get_recordHistory", options={"expose"=true})
	 * @Template("WoojinUserBundle:User:user.ajax.recordHistory.html.twig")
	 */
	public function userAjaxGetRecordHistoryAction(Request $request)
	{
		if ($request->getMethod() != 'POST')
			return new Response('error');

		foreach ($request->request->keys() as $key)
			$$key = $request->request->get($key);
		
		$oUser 		= $this->get('security.context')->getToken()->getUser();
		$em 		= $this->getDoctrine()->getManager();
		$qb 		= $em->createQueryBuilder();
		$sTimeEnd 	= date('Y-m-d H:i:s');
		$sTimeStart = date('Y-m-d H:i:s', strtotime('-3 Day'));

		$dql = ' op.datetime <=\'' . $sTimeEnd . '\' AND op.datetime >=\'' . $sTimeStart . '\'';
		$oRes = $qb
				->select('op')
				->from('WoojinOrderBundle:Ope', 'op')
				->where( 
					$qb->expr()->eq('op.user', $nId) 
				)
				->andWhere($dql)
				->orderBy('op.id', 'DESC')
				->getQuery()
			;
		
		$rOpe = $oRes->getResult();
		
		$dql = 'm.datetime <=\''.$sTimeEnd.'\' AND m.datetime >=\''.$sTimeStart.'\'';
		
		$oRes = $qb
				->select('m')
				->from('WoojinStoreBundle:MetaRecord', 'm')
				->where($qb->expr()->eq('m.user', $nId))
				->andWhere($dql)
				->orderBy('m.id', 'DESC')
				->getQuery()
			;

    	$rMetaRecord = $oRes->getResult();
		
		return array('rOpe' => $rOpe, 'rMetaRecord'=> $rMetaRecord);
	}

	/**
	 * @Route("/user/ajax_record_habit", name="user_ajax_record_habit", options={"expose"=true})
	 */
	public function ajaxRecordHabitAction(Request $request)
	{
		$user       = $this->get('security.context')->getToken()->getUser();
        $user_id    = $user->getId();
        $store_id   = $user->getId();

		foreach ($request->request->keys() as $key)
			$$key = $request->request->get($key);
		
		$con = array('user' => $user_id, 'name' => $users_habit_name);
		
		$users_habit = $this->getDoctrine()->getRepository('WoojinUserBundle:UsersHabit')->findOneBy($con);					

		if ($users_habit) {
			$users_habit->setValue($users_habit_value);
		} else {
			$users_habit = new UserHabit();
			$users_habit
				->setName($users_habit_name)
				->setValue($users_habit_value)
				->setUser($user)
			;	
		}

		$em = $this->getDoctrine()->getManager();
		$em->persist($users_habit);
		$em->flush();
    	
        return new Response('ok');									
	}

	
	/**
	 * @Route("/user/own_store_change", name="user_own_store_change", options={"expose"=true})
	 */
	public function userOwnStoreChange(Request $request)
	{
        $em = $this->getDoctrine()->getManager();

		$oUser = $this->get('security.context')->getToken()->getUser();
		
		$oUser->setStore($em->find('WoojinStoreBundle:Store', $request->request->get('store_id')));
		
		$em->persist($oUser);
		$em->flush();

		return new Response('ok');
	}
	
	/**
	 * @Route("/user/ajax_change_role", name="user_ajax_change_role", options={"expose"=true})
	 */
	public function userAjaxChaneRole(Request $request)
	{
        $em = $this->getDoctrine()->getManager();

        $oUserChange = $em->find('WoojinUserBundle:User', $request->request->get('id'));

        $roleId = ($oUserChange->getTheRoles()->getId() != self::ROLE_STAFF) 
			? self::ROLE_STAFF 
			: self::ROLE_OLD_STAFF
		;

        $oRoles = $em->find('WoojinUserBundle:Role', $roleId);

		$oUserChange->setRoles($oRoles);
		
		$em->persist($oUserChange);
		$em->flush();   

		$oMetaRecord = $this->get('my_meta_record_method');
        $oMetaRecord->recordMeta('更改權限為' . $oRoles->getName());

        return new Response($oRoles->getName());
	}
}




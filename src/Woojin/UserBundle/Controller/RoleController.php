<?php

namespace Woojin\UserBundle\Controller;

use Woojin\UserBundle\Entity\Role;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class RoleController extends Controller
{
    /**
    * @Route("/role", name="admin_role_index", options={"expose"=true})
    * @Template("WoojinUserBundle:Role:index.html.twig")
    * @Method("GET")
    */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $roles = $em->getRepository('WoojinUserBundle:Role')->findBy(array(), array('chmod' => 'DESC'));

        return array('roles' => $roles);
    }

    /**
    * @Route("/role/{id}/edit", requirements={"id"="\d+"}, name="admin_role_edit", options={"expose"=true})
    * @ParamConverter("role", class="WoojinUserBundle:Role")
    * @Template("WoojinUserBundle:Role:edit.html.twig")
    * @Method("GET")
    */
    public function editAction(Role $role)
    {
        return array(
            'role' => $role,
            'lists' => $this->getList()
        );
    }

    /**
    * @Route("/role/{id}", requirements={"id"="\d+"}, name="admin_role_update", options={"expose"=true})
    * @ParamConverter("role", class="WoojinUserBundle:Role")
    * @Method("PUT")
    */
    public function updateAction(Request $request, Role $role)
    {
        $em = $this->getDoctrine()->getManager();

        $lists = $this->getList();
        $a = array();

        for ($i = 0; $i <= 53; $i ++) {
            $a[] = null;
        }

        foreach ($lists as $key => $list) {
            $a[constant('Woojin\UserBundle\Entity\Role::' . strtoupper($key))] = (int) $request->request->get(strtolower($key), 0);
        }

        $role->setChmod(implode('', $a));

        $em->persist($role);
        $em->flush();

        $session = $this->get('session');
        $session->getFlashBag()->add('success', $role->getName() . '權限修改完成');

        return $this->redirect($this->generateUrl('admin_role_edit', array('id' => $role->getId())));
    }

    /**
    * @Route("/role/new", name="admin_role_new", options={"expose"=true})
    * @Template("WoojinUserBundle:Role:new.html.twig")
    * @Method("GET")
    */
    public function newAction()
    {
        return array('lists' => $this->getList());
    }

    /**
    * @Route("/role", name="admin_role_add", options={"expose"=true})
    * @Method("POST")
    */
    public function addAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $lists = $this->getList();
        $a = array();

        for ($i = 0; $i <= 53; $i ++) {
            $a[] = null;
        }

        foreach ($lists as $key => $list) {
            $a[constant('Woojin\UserBundle\Entity\Role::' . strtoupper($key))] = (int) $request->request->get(strtolower($key), 0);
        }

        $role = new Role;
        $role
            ->setName($request->request->get('name', '未命名'))
            ->setRole('ROLE_DEFAULT')
            ->setChmod(implode('', $a))
        ;

        $em->persist($role);
        $em->flush();

        $role->setRole('ROLE_DEFAULT_' . $role->getId());
        $em->persist($role);
        $em->flush();

        $session = $this->get('session');
        $session->getFlashBag()->add('success', $role->getName() . '新增完成');

        return $this->redirect($this->generateUrl('admin_role_index'));
    }

    /**
    * @Route("/role/{id}", requirements={"id"="\d+"}, name="admin_role_delete", options={"expose"=true})
    * @ParamConverter("role", class="WoojinUserBundle:Role")
    * @Method("DELETE")
    */
    public function deleteAction(Role $role)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($role);
        $em->flush();

        $session = $this->get('session');
        $session->getFlashBag()->add('success', $role->getName() . '權限刪除完成');

        return $this->redirect($this->generateUrl('admin_role_index', array('id' => $role->getId())));
    }

    protected function getList()
    {
        return Role::getMap();
    }
}

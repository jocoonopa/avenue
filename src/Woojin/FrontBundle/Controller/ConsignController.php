<?php

namespace Woojin\FrontBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\Utility\Avenue\Avenue;

/**
 * @Route("/consign")
 */
class ConsignController extends Controller
{
    const PERPAGE = 50;

    /**
     * login page
     *
     * @Route("/login", name="consign_login")
     * @Template()
     * @Method("GET")
     */
    public function loginAction()
    {
        return array();
    }

    /**
     * logout page
     *
     * @Route("/logout", name="consign_logout")
     * @Method("GET")
     */
    public function logoutAction()
    {
        $session = $this->get('session');
        $session->remove('mobil');
        $session->getFlashBag()->add('success', '');

        return $this->redirect($this->generateUrl('consign_login'));
    }

    /**
     * confirm login process
     *
     * @Route("/confirm", name="consign_login_confirm")
     * @Method("POST")
     */
    public function confirmAction(Request $request)
    {
        if (!$this->get('security.csrf.token_manager')->isCsrfTokenValid('consign_custom', $request->request->get('_csrf_token'))) {
            throw new AccessDeniedHttpException('Invalid CSRF token.');
        }

        $session = $this->get('session');

        if (!in_array($request->request->get('password'), array('avenue2003'))) {
            $session->getFlashBag()->add('error', 'error');

            return $this->redirect($this->generateUrl('consign_login'));
        }

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select('c')
            ->from('WoojinOrderBundle:Custom', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.mobil', $qb->expr()->literal($request->request->get('mobil')))
                )
            )
        ;

        $customs = $qb->getQuery()->getResult();

        if ($customs) {
            $session->set('mobil', $request->request->get('mobil'));

            return $this->redirect($this->generateUrl('consign_list'));
        } else {
            $session->getFlashBag()->add('error', 'error');

            return $this->redirect($this->generateUrl('consign_login'));
        }
    }

    /**
     * 預設按照 id排序, 欄位: 狀態, 產編, 品牌, 品名, 型號, 圖片(greg), 成本, 所在店
     *
     * @Route("/page/{page}", requirements={"id"="\d+"}, defaults={"page"="1"}, name="consign_list")
     * @Template("WoojinFrontBundle:Consign:list.html.twig")
     * @Method("GET")
     */
    public function listAction($page)
    {
        $session = $this->get('session');

        $mobil = $session->get('mobil');
        if (!$mobil) {
            return $this->redirect($this->generateUrl('consign_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.custom', 'gc')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('gc.mobil', ':mobil'),
                    $qb->expr()->in('g.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY))
                )
            )
            ->orderBy('g.id', 'DESC')
        ;

        $qb->setParameter('mobil', $mobil);

        $qb
            ->setFirstResult(($page - 1) * self::PERPAGE)
            ->setMaxResults(self::PERPAGE)
        ;

        $paginator = new Paginator($qb, $fetchJoinCollection = true);

        return array(
            'totalPage' => ceil(count($paginator) / self::PERPAGE),
            'products' => $paginator,
            'page' => $page
        );
    }

    /**
     * @Route("/export", name="consign_list_export")
     * @Method("GET")
     */
    public function exportAction()
    {
        $exporter = $this->get('exporter.consign');

        $session = $this->get('session');

        $mobil = $session->get('mobil');
        if (!$mobil) {
            return $this->redirect($this->generateUrl('consign_login'));
        }

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.custom', 'gc')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('gc.mobil', ':mobil'),
                    $qb->expr()->in('g.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY))
                )
            )
            ->orderBy('g.id', 'DESC')
        ;

        $qb->setParameter('mobil', $mobil);

        $products = $qb->getQuery()->getResult();

        $exporter->create($products);

        return $exporter->getResponse();
    }
}

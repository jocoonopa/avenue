<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Woojin\StoreBundle\Entity\Holiday;
use Woojin\UserBundle\Entity\User;

/**
 * Board controller.
 *
 * @Route("/holiday")
 */
class HolidayController extends Controller
{
    /**
     * @Route("/list/{date}", defaults={"date"=null}, name="admin_store_holiday_list")
     * @Template()
     * @Method("GET")
     */
    public function listAction($date = null)
    {
        $time = new \DateTime($date);

        $em = $this->getDoctrine()->getManager();
        
        $holidays = $em->getRepository('WoojinStoreBundle:Holiday')->findByDate($time);

        return array(
            'time' => $time,
            'holidays' => $holidays
        );
    }

    /**
     * @Route("/manage/{date}", defaults={"date"=null}, name="admin_store_holiday_manage")
     * @Template()
     * @Method("GET")
     */
    public function manageAction($date = null)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $time = new \DateTime($date);

        $em = $this->getDoctrine()->getManager();
        
        $holiday = $em->getRepository('WoojinStoreBundle:Holiday')->findOneByDateAndUser($time, $user);

        if (!$holiday) {
            $holiday = new Holiday;

            $schedule = array();

            for ($i = 1; $i <= 31; $i ++) {
                $schedule[$i] = 0;
            }

            $holiday
                ->setUser($user)
                ->setSchedule($schedule)
                ->setCreateAt($time)
            ;

            $em->persist($holiday);
            $em->flush();
        }

        return array(
            'time' => $time,
            'holiday' => $holiday
        );
    }

    /**
     * @Route("/manageother/{id}/{date}", defaults={"date"=null}, requirements={"id"="\d+"}, name="admin_store_holiday_manageother")
     * @ParamConverter("user", class="WoojinUserBundle:User")
     * @Template()
     * @Method("GET")
     */
    public function manageOtherAction(User $user, $date = null)
    {
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();

        if (!$currentUser->getRole()->hasAuth('EDIT_OTHER_HOLIDAY')) {
            throw new \Exception('權限不足');
        }

        $time = new \DateTime($date);

        $em = $this->getDoctrine()->getManager();
        
        $holiday = $em->getRepository('WoojinStoreBundle:Holiday')->findOneByDateAndUser($time, $user);

        if (!$holiday) {
            $holiday = new Holiday;

            $schedule = array();

            for ($i = 1; $i <= 31; $i ++) {
                $schedule[$i] = 0;
            }

            $holiday
                ->setUser($user)
                ->setSchedule($schedule)
                ->setCreateAt($time)
            ;

            $em->persist($holiday);
            $em->flush();
        }

        return array(
            'time' => $time,
            'holiday' => $holiday,
            'user' => $user
        );
    }

    /**
     * @Route("/{id}", requirements={"id"="\d+"}, name="admin_store_holiday_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, Holiday $holiday)
    {
        $schedule = $request->request->get('schedule');

        $holiday->setSchedule(array_replace($holiday->getSchedule(), $schedule));

        $em = $this->getDoctrine()->getManager();
        $em->persist($holiday);
        $em->flush();

        $session = $this->get('session');
        $session->getFlashBag()->add('success', '排假更新完成!');

        return $this->redirect($this->generateUrl('admin_store_holiday_list'));
    }
}
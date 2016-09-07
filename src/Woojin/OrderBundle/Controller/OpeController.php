<?php

namespace Woojin\OrderBundle\Controller;

//Third Party
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

//Component
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;

//Default
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

// Entity
use Woojin\OrderBundle\Entity\Ope;
use Woojin\UserBundle\Entity\AvenueClue;

/**
 * Ope controller.
 *
 * @Route("/ope")
 */
class OpeController extends Controller
{
	/**
   * @Route("/{id}/date", requirements={"id" = "\d+"}, name="update_ope_datetime",options={"expose"=true})
   * @ParamConverter("ope", class="WoojinOrderBundle:Ope")
   * @Method("PUT")
   *
   * @ApiDoc(
   *  resource=true,
   *  description="根據傳入的 id 更新指定記錄(ope)",
   *  requirements={{"name"="id", "dataType"="integer", "required"=true, "description"="操作記錄的 id "}},
   *  statusCodes={
   *    200="Returned when successful",
   *    404={
   *     "Returned when something else is not found"
   *    },
   *    500={
   *     "Please contact author to fix it"
   *    }
   *  }
   * )
   */
	public function updateDateAction(Request $request, Ope $ope) 
	{
		$em = $this->getDoctrine()->getManager();

		$date = $request->request->get('update_at');

      $sculper = $this->get('sculper.clue');
      $sculper->initModifyOpeDatetime();
      $sculper->setBefore($ope);

		$user = $this->get('security.token_storage')->getToken()->getUser();

		$roleId = $user->getTheRoles()->getId();

		$ope->setDatetime(new \DateTime($date));
      $sculper->setAfter($ope);

      $clue = new AvenueClue;
      $clue->setUser($user)->setContent($sculper->getContent());

      $em->persist($clue);
		$em->persist($ope);
		$em->flush();

		return new JsonResponse(array('status' => 1));
	}
}
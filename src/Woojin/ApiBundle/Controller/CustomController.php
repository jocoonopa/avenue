<?php

namespace Woojin\ApiBundle\Controller;

//Third Party
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Woojin\OrderBundle\Entity\Custom;
use Woojin\Utility\Handler\ResponseHandler;

class CustomController extends Controller
{
    /**
     * @Route("/custom/whishlist/{_format}", defaults={"_format"="json"}, name="api_custom_whishlist", options={"expose"=true})
     * @Method("GET")
     *
     * @ApiDoc(
     *  resource=true,
     *  description="取得該用戶的願望清單(json)"
     * )
     */
    public function whishlistAction(Request $request, $_format)
    {
        $custom = $this->get('session.custom')->current();

        $responseHandler = new ResponseHandler;

        if (!$custom) {
            return $responseHandler->getResponse('', $_format);
        }

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $jsonWhishlist = $serializer->serialize($custom->getWhishlist(), $_format);

        return $responseHandler->getResponse($jsonWhishlist, $_format);
    }

    /**
     * @Route("/custom/whishlist/add", name="api_custom_whishlist_add", options={"expose"=true})
     * @Method("PUT")
     *
     * @ApiDoc(
     *  description="將商品加入用戶的願望清單"
     * )
     */
    public function addWhishlistAction(Request $request)
    {
        $responseHandler = new ResponseHandler;

        $custom = $this->get('session.custom')->current();

        if (!$custom) {
            return $responseHandler->getResponse(json_encode(array('status' => 2, '')), 'json');
        }

        if (!$custom) {
            $msg = array(
                'status' => 4,
                'msg' => '請先登入會員!',
                'data' => $whishlist
            );

            return $responseHandler->getResponse(json_encode(array('status' => 2, '')), 'json');
        }

        $whishlist = json_decode($custom->getWhishlist(), true);
        $whishlist = (!is_array($whishlist)) ? array() : $whishlist;

        if (count($whishlist) > 20) {
            $msg = array(
                'status' => 3,
                'msg' => '您的願望清單已經超過上限20件囉!',
                'data' => $whishlist
            );

            return $responseHandler->getResponse(json_encode($msg), 'json');
        }

        if (!in_array($request->request->get('product_id'), $whishlist)) {
            $whishlist[] = $request->request->get('product_id');
        }

        $custom->setWhishlist(json_encode($whishlist));

        $em = $this->getDoctrine()->getManager();
        $em->persist($custom);
        $em->flush();

        $msg = array(
            'status' => 1,
            'msg' => 'ok',
            'data' => $whishlist
        );

        return $responseHandler->getResponse(json_encode($msg), 'json');
    }

    /**
     * @Route("/custom/whishlist/remove", name="api_custom_whishlist_remove", options={"expose"=true})
     * @Method("PUT")
     *
     * @ApiDoc(
     *  description="將商品從用戶的願望清單移除"
     * )
     */
    public function removeWhishlistAction(Request $request)
    {
        $custom = $this->get('session.custom')->current();

        if (!$custom) {
            return $responseHandler->getResponse(json_encode(array('status' => 2, '')), 'json');
        }

        $whishlist = json_decode($custom->getWhishlist(), true);
        $whishlist = (!is_array($whishlist)) ? array() : $whishlist;

        $key = array_search($request->request->get('product_id'), $whishlist);

        unset($whishlist[$key]);

        $custom->setWhishlist(json_encode($whishlist));

        $em = $this->getDoctrine()->getManager();
        $em->persist($custom);
        $em->flush();

        $responseHandler = new ResponseHandler;

        $msg = array(
            'status' => 1,
            'msg' => 'ok',
            'data' => $whishlist
        );

        return $responseHandler->getResponse(json_encode($msg), 'json');
    }

    /**
     * @Route("/custom/whishlist/empty", name="api_custom_whishlist_empty", options={"expose"=true})
     * @Method("PUT")
     *
     * @ApiDoc(
     *  description="清空用戶的願望清單"
     * )
     */
    public function emptyWhishlistAction()
    {
        $custom = $this->get('session.custom')->current();

        if (!$custom) {
            return $responseHandler->getResponse(json_encode(array('status' => 2, '')), 'json');
        }

        $custom->setWhishlist(array());

        $em = $this->getDoctrine()->getManager();
        $em->persist($custom);
        $em->flush();

        $responseHandler = new ResponseHandler;

        $msg = array(
            'status' => 1,
            'msg' => 'ok',
            'data' => $whishlist
        );

        return $responseHandler->getResponse(json_encode($msg), 'json');
    }
}

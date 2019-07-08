<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Lsw\ApiCallerBundle\Call\HttpGetJson;

class DefaultController extends Controller
{
    /**
     * @Route("/curlTest")
     * @Template()
     */
    public function curlTestAction()
    {
        $output = $this
            ->get('api_caller')
            ->call(
                new HttpGetJson('http://tw.ews.mall.yahooapis.com/stauth/v1/echo',
                array('Format' => 'json')
            ))
        ;

        return array('output' => $output);
    }

    /**
     * @Route("/stock_check", name="stock_check_load", options={"expose"=true})
     * @Method("GET")
     */
    public function stockCheckLoadAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $fileName = $user->getStore()->getStockCheckFileName();

        if (file_exists($fileName)) {
            return new Response(file_get_contents($fileName));
        }

        return new JsonResponse(array('wordStatus' => array(), 'dangerStatus' => array()));
    }

    /**
     * @Route("/stock_check", name="stock_check_update", options={"expose"=true})
     * @Method("PUT")
     */
    public function stockCheckUpdateAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $file = fopen($user->getStore()->getStockCheckFileName(), 'w');
        fwrite($file, $request->request->get('content'));
        fclose($file);

        return new JsonResponse(array());
    }
}

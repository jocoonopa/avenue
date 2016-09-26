<?php

namespace Woojin\ApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Woojin\UserBundle\Entity\User;
use Woojin\OrderBundle\Entity\Ope;
use Woojin\OrderBundle\Entity\Custom;
use Woojin\Utility\Avenue\Avenue;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class AuctionCustomController extends Controller
{
    use HelperTrait;

    /**
     * @Route(
     *      "/auction_custom/list/{_format}",
     *      defaults={"_format"="json"},
     *      name="api_auction_custom_show",
     *      options={"expose"=true}
     *  )
     * @ApiDoc(
     *  description="Fetch the customers fit the filter",
     *  filters={
     *      {"name"="mobil", "dataType"="string"},
     *      {"name"="name", "dataType"="string"}
     *  }
     * )
     * @Method("GET")
     */
    public function listAction(Request $request, $_format)
    {
        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * The Current User
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * The Custom repo fetch with criteria
         *
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy($this->genListCriteria($request, $user));

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getListActionValidaters(array($custom)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        return $this->_genResponseWithCustom($custom, $_format);
    }

    /**
     * @Route(
     *      "/auction_custom/{id}/{_format}",
     *      requirements={"id"="\d+"},
     *      defaults={"_format"="json"},
     *      name="api_auction_customer_show",
     *      options={"expose"=true}
     *  )
     * @ParamConverter("custom", class="WoojinOrderBundle:Custom")
     * @ApiDoc(
     *  description="Fetch the customer information",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="customer id"
     *      }
     *  },
     *  parameters={
     *      {"name"="_format", "dataType"="string", "required"=false, "description"="response format"}
     *  }
     * )
     * @Method("GET")
     */
    public function showAction($custom = NULL, $_format)
    {
        /**
         * The Current User
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getShowActionValidaters(array($custom, $user)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        return $this->_genResponseWithCustom($custom, $_format);
    }

    /**
     * @Route(
     *      "/auction_custom/{_format}",
     *      name="api_auction_customer_create",
     *      options={"expose"=true},
     *      defaults={"_format": "json"}
     *  )
     * @ApiDoc(
     *  description="Create a new custom",
     *  parameters={
     *      {"name"="name", "dataType"="string", "required"=true, "description"="name"},
     *      {"name"="mobil", "dataType"="string", "required"=true, "description"="mobil"},
     *      {"name"="sex", "dataType"="string", "required"=false, "description"="sex"},
     *      {"name"="address", "dataType"="string", "required"=false, "description"="address"},
     *      {"name"="birthday", "dataType"="string", "required"=false, "description"="birthday"},
     *      {"name"="email", "dataType"="string", "required"=false, "description"="email"}
     *  }
     * )
     * @Method("POST")
     */
    public function createAction(Request $request, $_format)
    {
        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * The Current User
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * The Custom repo fetch with criteria
         *
         * @var \Woojin\OrderBundle\Entity\Custom
         */
        $custom = $em->getRepository('WoojinOrderBundle:Custom')->findOneBy($this->genCreateCriteria($request, $user));

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getCreateActionValidaters(array($custom)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        try {
            $custom = new Custom;
            $custom
                ->setName($request->get('name'))
                ->setMobil($request->get('mobil'))
                ->setSex($request->get('sex'))
                ->setAddress($request->get('address'))
                ->setBirthday($request->get('birthday'))
                ->setEmail($request->get('email'))
                ->setStore($user->getStore())
            ;

            $em->persist($custom);
            $em->flush();

            return $this->_genResponseWithCustom($custom, $_format);
        } catch (\Exception $e) {
            return $this->_genResponseWithCustom($e, $_format);
        }
    }

    /**
     * @Route(
     *      "/auction_custom/{id}/{_format}",
     *      name="api_auction_customer_update",
     *      requirements={"id"="\d+"},
     *      options={"expose"=true},
     *      defaults={"_format": "json"}
     *  )
     * @ParamConverter("custom", class="WoojinOrderBundle:Custom")
     * @ApiDoc(
     *  description="Update custom information",
     *  parameters={
     *      {"name"="name", "dataType"="string", "required"=true, "description"="name"},
     *      {"name"="mobil", "dataType"="string", "required"=true, "description"="mobil"},
     *      {"name"="sex", "dataType"="string", "required"=false, "description"="sex"},
     *      {"name"="address", "dataType"="string", "required"=false, "description"="address"},
     *      {"name"="birthday", "dataType"="string", "required"=false, "description"="birthday"},
     *      {"name"="email", "dataType"="string", "required"=false, "description"="email"}
     *  }
     * )
     * @Method("PUT")
     */
    public function updateAction(Request $request, $custom = NULL, $_format)
    {
        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * The Current User
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getUpdateActionValidaters(array($custom, $user)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        try {
            $custom = new Custom;
            $custom
                ->setName($request->get('name'))
                ->setMobil($request->get('mobil'))
                ->setSex($request->get('sex'))
                ->setAddress($request->get('address'))
                ->setBirthday($request->get('birthday'))
                ->setEmail($request->get('email'))
                ->setStore($user->getStore())
            ;

            $em->persist($custom);
            $em->flush();

            return $this->_genResponseWithCustom($custom, $_format);
        } catch (\Exception $e) {
            return $this->_genResponseWithCustom($e, $_format);
        }
    }

    /**
     * @Route(
     *      "/auction_custom/{id}/{_format}",
     *      name="api_auction_customer_delete",
     *      requirements={"id"="\d+"},
     *      options={"expose"=true},
     *      defaults={"_format": "json"}
     *  )
     * @ParamConverter("custom", class="WoojinOrderBundle:Custom")
     * @ApiDoc(
     *  description="Remove a custom from database"
     * )
     * @Method("DELETE")
     */
    public function deleteAction($custom = NULL, $_format)
    {
        /**
         * DoctrineManager
         *
         * @var \Doctrine\ORM\EntityManager;
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * The Current User
         *
         * @var \Woojin\UserBundle\Entity\User
         */
        $user = $this->get('security.token_storage')->getToken()->getUser();

        /**
         * Store the valide result
         *
         * @var array
         */
        $unValid = $this->execValidaters($this->getDeleteActionValidaters(array($custom, $user)));

        if (!empty($unValid)) {
            return $this->_getResponse($unValid, $_format);
        }

        try {
            $em->remove($custom);
            $em->flush();

            return $this->_genResponseWithCustom($custom, $_format);
        } catch (\Exception $e) {
            return $this->_genResponseWithCustom($e, $_format);
        }

        return new Response;
    }

    /**
     * Provide the validators to listAction
     *
     * @param  array $compose
     * @return array
     */
    protected function getListActionValidaters(array $compose)
    {
        list($custom) = $compose;

        return array(
            'hasFoundCustom' => array(
                'params' => array($custom),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomNotFound'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
            ))
        );
    }

    protected function getShowActionValidaters(array $compose)
    {
        list($custom, $user) = $compose;

        return array(
            'hasFoundCustom' => array(
                'params' => array($custom),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomNotFound'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
            )),
            'isBelongUserStore' => array(
                'params' => array($custom, $user),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomNotBelongToThisStore'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
            )),
        );
    }

    protected function getCreateActionValidaters(array $compose)
    {
        list($custom) = $compose;

        return array(
            'hasNotFoundCustom' => array(
                'params' => array($custom),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomMobilDuplicate'),
                    'http_status_code' => Response::HTTP_METHOD_NOT_ALLOWED
            ))
        );
    }

    protected function getUpdateActionValidaters(array $compose)
    {
        list($custom, $user) = $compose;

        return array(
            'hasFoundCustom' => array(
                'params' => array($custom),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomNotFound'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
            )),
            'isBelongUserStore' => array(
                'params' => array($custom, $user),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomNotBelongToThisStore'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
            ))
        );
    }

    protected function getDeleteActionValidaters(array $compose)
    {
        list($custom, $user) = $compose;

        return array(
            'hasFoundCustom' => array(
                'params' => array($custom),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomNotFound'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
            )),
            'isBelongUserStore' => array(
                'params' => array($custom, $user),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomNotBelongToThisStore'),
                    'http_status_code' => Response::HTTP_NOT_FOUND
            )),
            'hasNoOrders' => array(
                'params' => array($custom),
                'response' => array(
                    'status' => Avenue::IS_ERROR,
                    'msg' => $this->get('translator')->trans('CustomHasOrder')
                )
            )
        );
    }

    /**
     * GenCriteria by request  and the auth user
     *
     * @param  \Symfony\Component\HttpFoundation\Request    $request
     * @param  \Woojin\UserBundle\Entity\User    $user
     * @return array
     */
    protected function genListCriteria(Request $request, User $user)
    {
        $criteria = array(
            'mobil' => $request->get('mobil'),
            'name' => $request->get('name'),
            'store' => $user->getStore()
        );

        return array_filter($criteria, array($this, '_isNotNull'));
    }

    /**
     * GenCriteria by request  and the auth user
     *
     * @param  \Symfony\Component\HttpFoundation\Request    $request
     * @param  \Woojin\UserBundle\Entity\User    $user
     * @return array
     */
    protected function genCreateCriteria(Request $request, User $user)
    {
        $criteria = array(
            'mobil' => $request->get('mobil'),
            'store' => $user->getStore()
        );

        return array_filter($criteria, array($this, '_isNotNull'));
    }
}

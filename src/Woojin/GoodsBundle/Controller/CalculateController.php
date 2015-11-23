<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class CalculateController extends Controller
{
    const GS_ONSALE = 1;
    const GS_ACTIVITY = 6;
    const CT_WOMEN = 1;
    const CT_MEN = 2;
    const CT_SECONDHAND = 3;

    /**
     * @Route("/calculate/all")
     */
    public function indexAction()
    {
        set_time_limit(0);

        ini_set('memory_limit', '1024M');

        $start = microtime(true);

        $entityNameArray = array(
            'Brand',
            'Pattern',
            'Promotion',
            'GoodsLevel'
        );

        $em = $this->getDoctrine()->getManager();

        /**
         * 所有分類
         * 
         * @var array{\Woojin\GoodsBundle\Entity\Category}
         */
        $categorys = $em->getRepository('WoojinGoodsBundle:Category')->findAll();

        foreach ($entityNameArray as $entityName) {
            $this->setEntityProductCount($entityName, $categorys);
        }

        $executeCost = microtime(true) - $start;

        return new Response($executeCost);
    }

    /**
     * @Route("/calculate/promotion")
     */
    public function promotionAction()
    {
        set_time_limit(0);

        ini_set('memory_limit', '1024M');

        $em = $this->getDoctrine()->getManager();

        $this->setEntityProductCount('Promotion', $em->getRepository('WoojinGoodsBundle:Category')->findAll());

        return new Response('ok');
    }

    /**
     * @Route("/calculate/product")
     */
    protected function setEntityProductCount($entityName, $categorys) 
    {
        $em = $this->getDoctrine()->getManager();

        $entitys = $em->getRepository('WoojinGoodsBundle:' . $entityName)->findAll();

        foreach ($entitys as $entity) {
            $goodses = $entity->getGoodsPassports();
            $count = 0;
            $womenCount = 0;
            $menCount = 0;
            $secondhandCount = 0;

            foreach ($goodses as $goods) {
                if ($goods->getIsAllowWeb() 
                    && in_array($goods->getStatus()->getId(), array(self::GS_ONSALE, self::GS_ACTIVITY))
                ) {
                    foreach ($categorys as $category) {
                        if ($goods->hasCategory($category)) {
                            switch ($category->getId()) 
                            {
                                case self::CT_WOMEN:
                                    $womenCount ++;
                                    break;

                                case self::CT_MEN:
                                    $menCount ++;
                                    break;

                                case self::CT_SECONDHAND:
                                    $secondhandCount ++;
                                    break;
                            }
                        }
                    }

                    $count ++;
                }
            }

            $entity
                ->setCount($count)
                ->setWomenCount($womenCount)
                ->setMenCount($menCount)
                ->setSecondhandCount($secondhandCount)
            ;
            $em->persist($entity);
        }
        
        $em->flush();
    }

    /**
     * @Route("/calculate/brand")
     */
    public function brandAction()
    {
        set_time_limit(0);

        ini_set('memory_limit', '1024M');

        $em = $this->getDoctrine()->getManager();

        $this->setEntityProductCount('Brand', $em->getRepository('WoojinGoodsBundle:Category')->findAll());

        return new Response('ok');
    }

    /**
     * @Route("/calculate/pattern")
     */
    public function patternAction()
    {
        set_time_limit(0);

        ini_set('memory_limit', '1024M');

        $em = $this->getDoctrine()->getManager();

        $this->setEntityProductCount('Pattern', $em->getRepository('WoojinGoodsBundle:Category')->findAll());

        return new Response('ok');
    }

    /**
     * @Route("/calculate/level")
     */
    public function levelAction()
    {
        set_time_limit(0);

        ini_set('memory_limit', '1024M');

        $em = $this->getDoctrine()->getManager();

        $this->setEntityProductCount('GoodsLevel', $em->getRepository('WoojinGoodsBundle:Category')->findAll());

        return new Response('ok');
    }
}

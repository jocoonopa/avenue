<?php

namespace Woojin\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Woojin\GoodsBundle\Entity\Category;
use Woojin\GoodsBundle\Entity\Promotion;

class FilterController extends Controller
{
    /**
     * @Route(
     *   "/category/{id}",
     *   requirements={"id"="\d+"},
     *   name="front_filter_category",
     *   options={"expose"=true}
     * )
     * @ParamConverter("category", class="WoojinGoodsBundle:Category", options={"id"="id"})
     * @Template("WoojinFrontBundle:Default:filter.html.twig")
     * @Method("GET")
     */
    public function filterAction(Category $category)
    {
        $options = array(
            'brand' => null,
            'pattern' => null,
            'promotion' => null,
            'category' => $category
        );

        return $this->getParameters($options);
    }

    /**
     * @Route(
     *   "/category/{id}/{entityName}/{entityId}", 
     *   defaults={
     *       "entityName"=null,
     *       "entityId"=null
     *   },
     *   requirements={"id"="\d+"},
     *   name="front_filter"
     * )
     * @ParamConverter("category", class="WoojinGoodsBundle:Category", options={"id"="id"})
     * @Template("WoojinFrontBundle:Default:filter.html.twig")
     * @Method("GET")
     */
    public function categoryAction(Category $category, $entityName, $entityId)
    {
        $options = $this->getEntityOptions($entityId, $entityName);
        $options['category'] = $category;

        return $this->getParameters($options);
    }

    /**
     * @Route("/search/{val}", name="front_text_filter", options={"expose"=true})
     * @Template("WoojinFrontBundle:Default:filter.html.twig")
     * @Method("GET")
     */
    public function textAction($val)
    {
        return $this->getParameters(array('val' => urldecode($val)));
    }

    /**
     * @Route("/jewels", name="front_filter_jewels")
     * @Template("WoojinFrontBundle:Default:filter.html.twig")
     * @Method("GET")
     */
    public function jewelsAction()
    {
        $fakePattern = new \stdClass();
        $fakePattern->id = 999;
        $fakePattern->name = '';
        $fakePattern->count = 0;

        $options = array(
            'brand' => null,
            'pattern' => $fakePattern,
            'promotion' => null,
            'jewel' => true,
            'category' => null
        );

        $options['isAll'] = true;

        return $this->getParameters($options);
    }

    /**
     * @Route(
     *   "/all/{entityName}/{entityId}", 
     *   requirements={"entityId"="\d+"},
     *   defaults={
     *       "entityName"=null,
     *       "entityId"=null
     *   },
     *   name="front_allcat_filter"
     * )
     * @Template("WoojinFrontBundle:Default:filter.html.twig")
     * @Method("GET")
     */
    public function allcatAction($entityName, $entityId)
    {
        $options = $this->getEntityOptions($entityId, $entityName);
        $options['isAll'] = true;

        return $this->getParameters($options);
    }
    

    /**
     * @Route(
     *   "/promotion/{id}", 
     *   requirements={"id"="\d+"},
     *   name="front_promotion_filter"
     * )
     * @ParamConverter("promotion", class="WoojinGoodsBundle:Promotion")
     * @Template("WoojinFrontBundle:Default:filter.html.twig")
     * @Method("GET")
     */
    public function promotionAction(Promotion $promotion)
    {
        if (!$promotion->isValid()) {
            throw $this->createNotFoundException('本活動已經結束或不存在!');
        }

        return $this->getParameters(array('promotion' => $promotion));
    }

    protected function getParameters(array $options)
    {
        $em = $this->getDoctrine()->getManager();

        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();

        $brands = $em->getRepository('WoojinGoodsBundle:Brand')->findValid();
        $patterns = $em->getRepository('WoojinGoodsBundle:Pattern')->findValid();
        $levels = $em->getRepository('WoojinGoodsBundle:GoodsLevel')->findValid();
        $promotions = $em->getRepository('WoojinGoodsBundle:Promotion')->findValid();

        $default =  array(
            'isAll' => null,
            'val' => null,
            'category' => null,
            'brand' => null,
            'pattern' => null,
            'promotion' => null,
            'jewel' => null,
            'brands' => $serializer->serialize($brands, 'json'),
            'patterns' => $serializer->serialize($patterns, 'json'),
            'levels' => $serializer->serialize($levels, 'json'),
            'promotions' => $serializer->serialize($promotions, 'json')
        );

        $resolver = new OptionsResolver();
        $resolver->setDefaults($default);

        return $resolver->resolve($options);
    }

    protected function getEntityOptions($entityId = null, $entityName = null)
    {
        $em = $this->getDoctrine()->getManager();

        $brand = null;
        $pattern = null;
        $promotion = null;

        if ($entityId) {
            switch ($entityName) 
            {
                case 'brand':
                    $brand = $em->find('WoojinGoodsBundle:Brand', $entityId);
                    break;

                case 'pattern':
                    $pattern = $em->find('WoojinGoodsBundle:Pattern', $entityId);
                    break;

                case 'promotion':
                    $promotion = $em->find('WoojinGoodsBundle:Promotion', $entityId);
                    break;

                default:
                    break;
            }
        }

        return array(
            'brand' => $brand,
            'pattern' => $pattern,
            'promotion' => $promotion
        );
    }
}
<?php

namespace Woojin\GoodsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Woojin\GoodsBundle\Entity\Color;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\GoodsBundle\Form\ColorType;


/**
 * Color controller.
 *
 * @Route("/color")
 */
class ColorController extends Controller
{
    /**
     * Import color 
     *
     * @Route("/import/csv", name="color_import_csv")
     * @Method("GET")
     */
    public function importAction()
    {
        // $colorRows = fopen($this->get('kernel')->getRootDir() . '/../web/color/color.csv', 'r');

        // $em = $this->getDoctrine()->getManager();

        // while (!feof($colorRows)) {
        //     $colorRow = fgetcsv($colorRows);

        //     $colorString = $colorRow[0];

        //     $color = new Color($this->getColorNameFromStr($colorString), $this->getColorCodeFromStr($colorString));

        //     $em->persist($color);
        // }    

        // $em->flush();

        return new Response('ok');
    }

    protected function getColorNameFromStr($colorString)
    {
        $pos = strpos($colorString, '#');

        return trim(substr($colorString, 0, $pos));
    }

    protected function getColorCodeFromStr($colorString)
    {
        $pos = strpos($colorString, '#');

        return trim(substr($colorString, $pos, 7));
    }

    /**
     * Lists all Color entities.
     *
     * @Route("/", name="color")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('WoojinGoodsBundle:Color')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Color entity.
     *
     * @Route("/", name="color_create")
     * @Method("POST")
     * @Template("WoojinGoodsBundle:Color:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Color();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('color_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Color entity.
     *
     * @param Color $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Color $entity)
    {
        $form = $this->createForm(new ColorType(), $entity, array(
            'action' => $this->generateUrl('color_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => '新增'));

        return $form;
    }

    /**
     * Displays a form to create a new Color entity.
     *
     * @Route("/new", name="color_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Color();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Color entity.
     *
     * @Route("/{id}", name="color_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Color')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('沒有這個顏色喔!');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Color entity.
     *
     * @Route("/{id}/edit", name="color_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Color')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('沒有這個顏色喔!');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Color entity.
    *
    * @param Color $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Color $entity)
    {
        $form = $this->createForm(new ColorType(), $entity, array(
            'action' => $this->generateUrl('color_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form
            ->add('submit', 'submit', array('label' => '更新'))
        ;

        return $form;
    }
    /**
     * Edits an existing Color entity.
     *
     * @Route("/{id}", name="color_update")
     * @Method("PUT")
     * @Template("WoojinGoodsBundle:Color:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('WoojinGoodsBundle:Color')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Color entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('color_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Color entity.
     *
     * @Route("/{id}", name="color_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('WoojinGoodsBundle:Color')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('沒有這個顏色喔!');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('color'));
    }

    /**
     * Creates a form to delete a Color entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('color_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => '刪除'))
            ->getForm()
        ;
    }

    /**
     * @Route("/omg/fix", name="color_omg_fix")
     * @Template()
     */
    public function omgFixAction()
    {
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();

        $qb->select(array('c', 'g'))
            ->from('WoojinGoodsBundle:Color', 'c')
            ->leftJoin('c.goodsPassports', 'g')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->in('c.id', array(2, 16, 18, 60, 66, 104, 105, 106))
                    //$qb->expr()->gte('c.id', 34)
                )
            )
        ;

        $colors = $qb->getQuery()->getResult();

        return array('colors' => $colors); 
    }

    /**
     * @Route("/throw/rebuild/{id}", name="throw_rebuild")
     * @Method("GET")
     */
    public function throwAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select(array('c', 'g'))
            ->from('WoojinGoodsBundle:Color', 'c')
            ->leftJoin('c.goodsPassports', 'g')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->in('c.id', array(2, 16, 18, 60, 66, 104, 105, 106))
                )
            )
        ;

        $colors = $qb->getQuery()->getResult();

        $array = array();
        // $product = $em->find('WoojinGoodsBundle:GoodsPassport', $id);
        // $array[] = $this->genArr($product);
        // $content = '';
        // foreach ($array as $eachArr) {
        //     $content .= $this->cURLPost('http://www.avenue2003.com/app_dev.php/que/catch/repairment', $eachArr) . "<br />";
        // }

        // foreach ($array as $eachArr) {
        //     $content .= $this->cURLPost('http://www.avenue2003.com/que/catch/rebindParent', $eachArr) . "<br />";
        // }

        $content = '';
        foreach ($colors as $color) {
            foreach ($color->getGoodsPassports() as $product) {
                $array[] = $this->genArr($product);
            }

            foreach ($array as $eachArr) {
                $content .= $this->cURLPost('http://www.avenue2003.com/que/catch/repairment', $eachArr) . "<br />";
            }

            // foreach ($array as $eachArr) {
            //     $content .= $this->cURLPost('http://www.avenue2003.com/que/catch/rebindParent', $eachArr) . "<br />";
            // }

            $array = array();
        }

        return new Response($content);
    }

    protected function genArr(GoodsPassport $product)
    {
        $arr = array();

        $arr['auth_key'] = 'jasodijQQ3Eoinaaoidll';

        // GoodsPassport (分類???)
        $arr['GoodsPassport']['id'] = $product->getId();
        $arr['GoodsPassport']['status_id'] = $product->getStatus()->getId();
        $arr['GoodsPassport']['source_id'] = ($product->getSource()) ? $product->getSource()->getId() : 0;
        $arr['GoodsPassport']['img_id'] = ($product->getImg()) ? $product->getImg()->getId() : 0;
        $arr['GoodsPassport']['level_id'] = ($product->getLevel()) ? $product->getLevel()->getId() : 0;
        $arr['GoodsPassport']['inherit_id'] = $product->getParent()->getId();
        $arr['GoodsPassport']['sn'] = $product->getSn();
        $arr['GoodsPassport']['name'] = $product->getName();
        $arr['GoodsPassport']['cost'] = $product->getCost();
        $arr['GoodsPassport']['price'] = $product->getPrice();
        $arr['GoodsPassport']['org_sn'] = $product->getOrgSn();
        $arr['GoodsPassport']['memo'] = $product->getMemo();
        $arr['GoodsPassport']['created_at'] = $product->getCreatedAt()->format('Y-m-d H:i:s');
        $arr['GoodsPassport']['mt_id'] = ($product->getMt()) ? $product->getMt()->getId() : 0;
        $arr['GoodsPassport']['activity_id'] = ($product->getActivity()) ? $product->getActivity()->getId() : 0;
        $arr['GoodsPassport']['custom_id'] = ($product->getCustom()) ? $product->getCustom()->getId() : 0;
        $arr['GoodsPassport']['brand_id'] = ($product->getBrand()) ? $product->getBrand()->getId() : 0;
        //$arr['GoodsPassport']['color_id'] = $product->;
        $arr['GoodsPassport']['pattern_id'] = ($product->getPattern()) ? $product->getPattern()->getId() : 0;
        $arr['GoodsPassport']['colorSn'] = $product->getColorSn();
        $arr['GoodsPassport']['model'] = $product->getModel();
        $arr['GoodsPassport']['customSn'] = $product->getCustomSn();
        $arr['GoodsPassport']['desimg_id'] = ($product->getDesimg()) ? $product->getDesimg()->getId() : 0;
        $arr['GoodsPassport']['description_id'] = ($product->getDescription()) ? $product->getDescription()->getId() : 0;
        $arr['GoodsPassport']['brief_id'] = ($product->getBrief()) ? $product->getBrief()->getId() : 0;
        $arr['GoodsPassport']['store_id'] = ($product->getStore()) ? $product->getStore()->getId() : 0;
        $arr['GoodsPassport']['promotion_id'] = ($product->getPromotion()) ? $product->getPromotion()->getId() : 0;
        $arr['GoodsPassport']['is_allow_web'] = $product->getIsAllowWeb();
        $arr['GoodsPassport']['update_at'] = ($product->getUpdateAt()) ? $product->getUpdateAt()->format('Y-m-d H:i:s') : null;
        $arr['GoodsPassport']['web_price'] = $product->getWebPrice();

        $arr['GoodsPassport']['categorys'] = array(0);

        foreach ($product->getCategorys() as $category) {
            $arr['GoodsPassport']['categorys'][] = $category->getId();
        }

        $i = 0;
        // Orders
        foreach ($product->getOrders() as $order) {
            $arr['Orders'][$i]['id'] = $order->getId();
            $arr['Orders'][$i]['goods_passport_id'] = $product->getId();
            $arr['Orders'][$i]['pay_type_id'] = ($order->getPayType()) ? $order->getPayType()->getId() : 0;
            $arr['Orders'][$i]['status_id'] = $order->getStatus()->getId();
            $arr['Orders'][$i]['kind_id'] = $order->getKind()->getId();
            $arr['Orders'][$i]['custom_id'] = ($order->getCustom()) ? $order->getCustom()->getId() : 0;
            $arr['Orders'][$i]['memo'] = $order->getMemo();
            $arr['Orders'][$i]['required'] = $order->getRequired();
            $arr['Orders'][$i]['paid'] = $order->getPaid();
            $arr['Orders'][$i]['relate_id'] = ($order->getParent()) ? $order->getParent()->getId() : 0;
            $arr['Orders'][$i]['invoice_id'] = ($order->getInvoice()) ? $order->getInvoice()->getId() : 0;
            $arr['Orders'][$i]['org_required'] = $order->getOrgRequired();
            $arr['Orders'][$i]['org_paid'] = $order->getOrgPaid();

            // Ope
            $j = 0;
            foreach ($order->getOpes() as $ope) {
                //$arr['Ope'][]['orders_id'] = $order->get;
                $arr['Ope'][$order->getId()][$j]['user_id'] = ($ope->getUser()) ? $ope->getUser()->getId() : 0;
                $arr['Ope'][$order->getId()][$j]['act'] = $ope->getAct();
                $arr['Ope'][$order->getId()][$j]['datetime'] =  $ope->getDatetime()->format('Y-m-d H:i:s');
                $arr['Ope'][$order->getId()][$j]['memo'] = $ope->getMemo();

                $j ++;
            }

            $i ++;
        }

        return $arr;
    }

    /**
     * 透過cURL傳遞$_POST資料取得結果
     * 
     * @param  string $url  目標路徑
     * @param  array  $post 傳遞的資料
     * @return string         
     */
    public function cURLPost($url, array $post) 
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $sRes = curl_exec($ch);
        
        curl_close($ch);

        return $sRes;
    }
}

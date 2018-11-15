<?php

namespace Woojin\StoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Woojin\Utility\Avenue\Avenue;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * BenefitRule controller.
 *
 * @Route("/fix")
 */
class FixController extends Controller
{
    /**
     * 修正調貨後, 網路售價 NULL 的問題
     * 
     * @Route("/webpricenull", name="fix_webpricenull")
     * @Method("GET")
     */
    public function moveWebPriceNullProblem(Request $request) 
    {
        if ('jocoonopa' !== $request->query->get('token')) {
            throw $this->createNotFoundException('Token invalid'); 
        }

        set_time_limit(0);

        $batchSize = 30;
        $i = 0;
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select(array('m', 'org', 'new'))
            ->from('WoojinGoodsBundle:Move', 'm')
            ->leftJoin('m.orgGoods', 'org')
            ->leftJoin('m.newGoods', 'new')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->gt('org.webPrice', 0),
                    $qb->expr()->eq('new.webPrice', 0),
                    $qb->expr()->eq('org.status', Avenue::GS_OTHERSTORE),
                    $qb->expr()->in('new.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY))
                )
            )
        ;

        $iterableResult = $qb->getQuery()->iterate();
       
        foreach ($iterableResult as $row) {
            $move = $row[0];
            $org = $move->getOrgGoods();
            $new = $move->getNewGoods();

            echo $new->getSn() . "<br />";
            
            $new->setWebPrice($org->getWebPrice());

            $em->persist($new);

            if (0 === ($i % $batchSize)) {
                $em->flush();
                $em->clear(); // Detaches all objects from Doctrine!
            }

            ++ $i;
        }

        $em->flush();

        return new Response('ok, 共' . $i . '筆');
    }

    /**
     * fix_nothere
     * 
     * @Route("/nothere", name="fix_nothere", options={"expose"=true})
     * @Method("GET")
     */
    public function notHere(Request $request)
    {
        $sn = trim($request->query->get('sn'));

        $em = $this->getDoctrine()->getManager();

        $price = ((int) substr($sn, -4)) * 100;
        $cost = ((int) substr($sn, 2, 1) . substr($sn, 4, 1) . substr($sn, 6, 1) . substr($sn, 8, 1)) * 100;

        // 反解譯產編序號 2, 4, 6, 8 => 1 + 8 , 6, 4 , 2 (id > 10000)
        $guessId = '1' . substr($sn, 7, 1) . substr($sn, 5, 1) . substr($sn, 3, 1) . substr($sn, 1, 1);
        $guessId = (int) $guessId;

        $product = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findGuessOne($guessId, $price, $cost);

        if ($product) {
            $product->setSn($sn);

            $em->persist($product);
            $em->flush();

            return new JsonResponse(array('status' => '1|ok'));
        } 

        // 2, 4, 6, 8 (id < 10000)
        $guessId = substr($sn, 1, 1) . substr($sn, 3, 1) . substr($sn, 5, 1) . substr($sn, 7, 1);

        $product = $em->getRepository('WoojinGoodsBundle:GoodsPassport')->findGuessOne($guessId, $price, $cost);

        if ($product) {
            $product->setSn($sn);

            $em->persist($product);
            $em->flush();

            return new JsonResponse(array('status' => '1|ok'));
        }

        return new JsonResponse(array('status' => '0|no'));
    }

    /**
     * 移除切割圖片
     *
     * @Route("/removesplicedesimg", name="fix_removesplicedesimg")
     * @Method("GET")
     */
    public function removeSpliceDesImg(Request $request) 
    {
        if ('jocoonopa' !== $request->query->get('token')) {
            throw $this->createNotFoundException('Token invalid'); 
        }

        set_time_limit(0);

        $batchSize = 30;
        $i = 0;
        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb
            ->select(array('g', 'des'))
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.desimg', 'des')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->in('g.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY)),
                    $qb->expr()->isNotNull('g.desimg'),
                    $qb->expr()->gte('g.id', 17000)
                )
            )
        ;

        $iterableResult = $qb->getQuery()->iterate();
       
        foreach ($iterableResult as $row) {
            $product = $row[0];
            $des = $product->getDesimg();

            for ($i = 0; $i < 5; $i ++) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . $des->getSplitPath($i))) {
                    echo $des->getSplitPath($i);
                    unlink($_SERVER['DOCUMENT_ROOT'] . $des->getSplitPath($i));
                }
            }

            echo "<br />";
        }

        return new Response('');
    }

    /**
     * tag img and des img trashed
     *
     * @Route("/tag-image-trashed", name="tag_image_trashed")
     * @Method("GET")
     */
    public function tagImgTrashed()
    {
        set_time_limit(0);
        
        $date = new \DateTime();
        $updateAt = $date->modify('-6 months')->format('Y-m-d');

        $em = $this->getDoctrine()->getManager();

        $sql = "UPDATE desimg LEFT JOIN goods_passport ON desimg.id = goods_passport.img_id SET desimg.isTrashed = true WHERE goods_passport.status_id in (2, 10) and goods_passport.updateAt <= '{$updateAt}'";

        $em->getConnection()->exec($sql);

        $sql = "UPDATE img LEFT JOIN goods_passport ON img.id = goods_passport.img_id SET img.isTrashed = true WHERE goods_passport.status_id in (2, 10) and goods_passport.updateAt <= '{$updateAt}'";

        $em->getConnection()->exec($sql);

        return new Response('');
    }

    /**
     * 刪除圖片
     * 
     * @Route("/remove-image", name="remove_image")
     * @Method("GET")
     */
    public function removeImage(Request $request) 
    {
        set_time_limit(0);
        $max = 10000000;
        $size = 100;

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb2 = $em->createQueryBuilder();

        $qb->select('img')->from('WoojinGoodsBundle:Img', 'img')
            ->where(
                $qb->expr()->eq('img.isTrashed', true)
            )
        ;

        $qb2->select('COUNT(img)')->from('WoojinGoodsBundle:Img', 'img')
            ->where(
                $qb2->expr()->eq('img.isTrashed', ':key')
            )
        ;

        $qb2->setParameter('key', false);

        echo $qb2->getQuery()->getSQL();

        $total = $qb2->getQuery()->getSingleScalarResult();

        echo $total;

        for ($i = 0; $i < $total; $i = $i + $size) {
            if ($i > $max) {
                break;
            }
            
            $qb
                ->setFirstResult($i)
                ->setMaxResults($i + $size)
            ;

            $imgs = $qb->getQuery()->getResult();

            foreach ($imgs as $key => $img) {
                echo $img->getPath() . ': is-trashed = ' . $img->getIsTrashed() . "<br/>";

                if (false !== strpos($img->getPath(), '.jpg')) {
                    echo "{$img->getId()}刪除之!";
                }
            }
        }

        return new Response('');
    }

    /**
     * 刪除圖片
     * 
     * @Route("/remove-desimage", name="remove_desimage")
     * @Method("GET")
     */
    public function removeDesImage(Request $request) 
    {
        set_time_limit(0);
        $max = 10000000;
        $size = 100;

        $em = $this->getDoctrine()->getManager();

        $qb = $em->createQueryBuilder();
        $qb2 = $em->createQueryBuilder();

        $qb->select('img')->from('WoojinGoodsBundle:Desimg', 'img')
            ->where(
                $qb->expr()->eq('img.isTrashed', true)
            )
        ;

        $qb2->select('COUNT(img)')->from('WoojinGoodsBundle:Desimg', 'img')
            ->where(
                $qb2->expr()->eq('img.isTrashed', ':key')
            )
        ;

        $qb2->setParameter('key', false);

        echo $qb2->getQuery()->getSQL();

        $total = $qb2->getQuery()->getSingleScalarResult();

        echo '<br/>總共' . $total . "<br/>";

        for ($i = 0; $i < $total; $i = $i + $size) {
            if ($i > $max) {
                break;
            }
            
            $qb
                ->setFirstResult($i)
                ->setMaxResults($i + $size)
            ;

            $imgs = $qb->getQuery()->getResult();

            foreach ($imgs as $key => $img) {
                echo $img->getPath() . ': is-trashed = ' . $img->getIsTrashed() . "<br/>";

                if (false !== strpos($img->getPath(), '.jpg')) {
                    echo "{$img->getId()}刪除之!";
                }
            }
        }

        return new Response('');
    }

    /**
     * 刪除空資料夾
     * 
     * @Route("/remove-empty", name="remove_empty")
     * @Method("GET")
     */
    public function removeEmpty(Request $request)
    {
        set_time_limit(0);
        // /Users/jocoonopa/git/avenue/src/Woojin/StoreBundle/Controller/FixController.php
        $this->removeEmptySubFolders(__DIR__ . '/../../../../web/img');

        return new Response('');
    }

    protected function removeEmptySubFolders($path) 
    {
        $empty = true;

        foreach (glob($path.DIRECTORY_SEPARATOR."*") as $file) {
            if (is_dir($file)) {
                if (!$this->removeEmptySubFolders($file)) {
                    $empty = false;
                }
            } else {
                $empty = false;
            }
        }
        
        if ($empty) {
            echo "移除資料夾 {$path} <br/>";
            rmdir($path);
        }
        
        return $empty;
    }
}











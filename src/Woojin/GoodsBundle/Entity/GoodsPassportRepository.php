<?php

namespace Woojin\GoodsBundle\Entity;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Woojin\StoreBundle\Entity\Store; 
use Woojin\UserBundle\Entity\User; 
use Woojin\GoodsBundle\Entity\GoodsPassport; 
use Woojin\Utility\Avenue\Avenue;

class GoodsPassportRepository extends EntityRepository
{
    public function findByIds(array $ids)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where($qb->expr()->andX($qb->expr()->in('g.id', $ids)))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findStoreOnSale($store_sn)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->join('g.orders', 'od')
            ->join('od.opes', 'op')
            ->join('op.user', 'u')
            ->where( 
                $qb->expr()->andX(
                    $qb->expr()->eq('g.status', Avenue::GS_ONSALE),
                    $qb->expr()->eq('SUBSTRING(g.sn, 1, 1)', $qb->expr()->literal($store_sn))
                ) 
            )
            ->getQuery()
            ->getResult()
        ;
    }

    public function findGuessOne($id, $price, $cost)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select('g')
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->eq('g.id', $id),
                        $qb->expr()->eq('g.parent', $id)
                    ),
                    $qb->expr()->in('g.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY)),
                    $qb->expr()->eq('g.price', $price),
                    $qb->expr()->eq('g.cost', $cost)
                )
            )
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAssignStoreOnSaleJoinBrand($storeSn)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select(array('gp', 'b'))
            ->from('WoojinGoodsBundle:GoodsPassport' , 'gp')
            ->join('gp.brand', 'b')
            ->where( 
                $qb->expr()->andX(
                    $qb->expr()->eq('gp.status', Avenue::GS_ONSALE),
                    $qb->expr()->eq( 
                        $qb->expr()->substring('gp.sn', 1, 1), 
                        $qb->expr()->literal($storeSn)
                    )
                )
            )
            ->groupBy('gp.id')
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findIsActivityByGoodsSn($goodsSn)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->select('gd')
            ->from('WoojinGoodsBundle:GoodsPassport', 'gd')
            ->where( 
                $qb->expr()->andX( 
                    $qb->expr()->eq('gd.sn', $qb->expr()->literal($goodsSn)), 
                    $qb->expr()->eq('gd.status', Avenue::GS_ACTIVITY)
                )  
            )
            ->getQuery()
            ->getResult()
        ;
    }

    public function getConsignCompleteAtHome(User $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select(array('g','o'))
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->join('g.orders', 'o')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq(
                        $qb->expr()->substring('g.sn', 1, 1), 
                        $qb->expr()->literal($user->getStore()->getSn())
                    ),
                    $qb->expr()->eq('g.status', Avenue::GS_SOLDOUT),
                    $qb->expr()->isNotNull('g.custom'),
                    $qb->expr()->eq('o.status', Avenue::OS_HANDLING),
                    $qb->expr()->eq('o.kind', Avenue::OK_FEEDBACK)
                )
            )
        ;

        return $qb->getQuery()->getResult();
    }

    public function getConsignFromOurStore(User $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select(array('g', 'o', 'p'))
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.orders', 'o')
            ->leftJoin('g.parent', 'p')
            ->leftJoin('p.orders', 'po')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('g.status', Avenue::GS_OTHERSTORE),
                    $qb->expr()->eq('po.kind', Avenue::OK_FEEDBACK),
                    $qb->expr()->eq('po.status', Avenue::OS_HANDLING),
                    $qb->expr()->eq('SUBSTRING(p.sn, 1, 1)', $qb->expr()->literal($user->getStore()->getSn()))
                )
            )
        ;

        return $qb->getQuery()->getResult();
    }
    
    //=== 取得相關商品 ===//
    /**
     * 取得該商品的相關商品
     * 
     * @param  GoodsPassport $goods 
     * @return Collention GoodsPassport              
     */
    public function getRelative(GoodsPassport $goods){}
    //=== End 取得相關商品 ===//
    
    /**
     * 亂數取得上架商品
     *
     * 從最新的300樣商品亂數取 $num 件
     *
     * @param  integer $num 
     * @return Collention GoodsPassport
     */
    public function getRandOnSale($num = 1)
    {
        $rands = array();

        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select(array('g', 'p', 'i', 'bd', 'l', 'd', 'bf'))
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.brand', 'bd')
            ->leftJoin('g.pattern', 'p')
            ->leftJoin('g.img', 'i')
            ->leftJoin('g.level', 'l')
            ->leftJoin('g.description', 'd')
            ->leftJoin('g.brief', 'bf')
            ->leftJoin('g.categorys', 'c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->orX(
                        $qb->expr()->in('g.status', array(
                            Avenue::GS_ONSALE, 
                            Avenue::GS_ACTIVITY
                        )),
                        $qb->expr()->andX(
                            $qb->expr()->in('g.status', array(
                                Avenue::GS_SOLDOUT,
                                Avenue::GS_BEHALF
                            )),
                            $qb->expr()->eq('g.isBehalf', true) 
                        )
                    ),
                    $qb->expr()->eq('g.isAllowWeb', true),
                    $qb->expr()->isNotNull('g.img'),
                    $qb->expr()->isNotNull('g.description'),
                    $qb->expr()->isNotNull('g.brief'),
                    $qb->expr()->isNotNull('g.desimg')
                )
            )
            ->orderBy('g.id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(300)
        ;

        $arrayOfGoods = $qb->getQuery()->getResult();

        if (!empty($arrayOfGoods)) {
            $keys = array_rand($arrayOfGoods, (count($arrayOfGoods) < $num) ? count($arrayOfGoods) : $num);       

            foreach ($keys as $key) {
                $rands[] = $arrayOfGoods[$key];
            }
        }

        return $rands;
    }

    /**
     * 取得促銷活動中新舊程度最高的12筆商品
     * 
     * @return array
     */
    public function getTimelineProducts($num = 1)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb
            ->select(array('g', 'p', 'i', 'bd', 'l', 'd', 'bf', 'c'))
            ->from('WoojinGoodsBundle:GoodsPassport', 'g')
            ->leftJoin('g.brand', 'bd')
            ->leftJoin('g.pattern', 'p')
            ->leftJoin('g.img', 'i')
            ->leftJoin('g.level', 'l')
            ->leftJoin('g.description', 'd')
            ->leftJoin('g.brief', 'bf')
            ->leftJoin('g.categorys', 'c')
            ->where(
                $qb->expr()->in('g.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY)),
                $qb->expr()->eq('g.isAllowWeb', true),
                $qb->expr()->isNotNull('g.img'),
                $qb->expr()->isNotNull('g.description'),
                $qb->expr()->isNotNull('g.brief'),
                $qb->expr()->isNotNull('g.desimg'),
                $qb->expr()->isNotNull('g.promotion')
            )
            ->orderBy('g.level', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($num)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * 多筆銷貨和促銷活動的介面都用這個方法去取得資料~
     * 
     * @param  Request $request
     * @param  Store   $store  
     * @return array
     */
    public function getMultile(Request $request, Store $store)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('gd')
            ->from('WoojinGoodsBundle:GoodsPassport', 'gd') 
            ->where( 
                //$qb->expr()->andX(
                    //$qb->expr()->eq('SUBSTRING(gd.sn, 1, 1)', $qb->expr()->literal($store->getSn())),
                    $qb->expr()->in('gd.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY))
                //)
            )
            ->groupBy('gd.id')
            ->orderBy('gd.sn')
        ;

        if (($brandId = $request->request->get('brand')) != 0) {
            $qb->andWhere($qb->expr()->eq('gd.brand', $brandId));
        }

        if (($patternId = $request->request->get('pattern')) != 0) {
            $qb->andWhere($qb->expr()->eq('gd.pattern', $patternId));
        }

        if (($colorId = $request->request->get('color')) != 0) {
            $qb->andWhere($qb->expr()->eq('gd.color', $colorId));
        }

        if (($goodsSn = $request->request->get('sGoodsSn')) != '') {
            $qb->andWhere($qb->expr()->eq('gd.sn', $qb->expr()->literal($goodsSn)));
        }

        if (($goodsSn = $request->request->get('sn')) != '') {
            $qb->andWhere($qb->expr()->eq('gd.sn', $qb->expr()->literal($goodsSn)));
        }

        if (($promotionId = $request->request->get('promotion')) !=  0) {
            $qb->andWhere($qb->expr()->eq('gd.promotion', $promotionId));
        }

        if ($isAllowWeb = $request->request->get('isAllowWeb')) {
            $qb->andWhere($qb->expr()->eq('gd.isAllowWeb', $isAllowWeb));
        }
        
        return $qb->getQuery()->getResult();
    }

    public function findBsoProductsUserStoreOwn(Store $store)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('gd')
            ->from('WoojinGoodsBundle:GoodsPassport', 'gd') 
            ->where( 
                $qb->expr()->andX(
                    $qb->expr()->eq('SUBSTRING(gd.sn, 1, 1)', $qb->expr()->literal($store->getSn())),
                    $qb->expr()->eq('gd.status', Avenue::GS_BSO_ONBOARD)
                )
            )
            ->groupBy('gd.id')
            ->orderBy('gd.sn')
        ;

        return $qb->getQuery()->getResult();
    }
}




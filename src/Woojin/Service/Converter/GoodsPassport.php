<?php

namespace Woojin\Service\Converter;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Woojin\Utility\Avenue\Avenue;

/**
 * 本類別是用來處理官網前台的搜尋商務邏輯。
 *
 * 前台透過 JSON 拋來的 condition 物件轉換為 query 的動作頗為複雜，
 * 因此獨立出一個專門針對 "condition 轉換成 query" 的類別來處理
 */
class GoodsPassport
{
    const GOODS_ALIAS       = 'gd';
    const CATEGORY_ALIAS    = 'c';
    const PER_PAGE          = 30;

    private $em;

    private $qb;

    private $condition;

    private $paginator;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
        $this->qb = $this->em->createQueryBuilder();
    }

    /**
     * 取得根據條件產生和擴充 query
     * 
     * @param  stdClass $condition
     * @param  boolean $isCount  [請求的是否為計數]
     * @param  boolean $isPrint  [是否要印出實際執行的 DQL]
     * @param  boolean $isWeb    [是否為官網fetch，若是增加考慮售出+允許代購條件]
     * @return $this->qb          
     */
    public function gen($condition, $isCount = false, $isPrint = false, $isWeb = false)
    {
        $this->setCondition($condition);

        $this
            ->init()
            ->extend($isCount)
            ->setParameter()
            ->paginate()
        ;

        if ($isPrint) {
            $this->_print();
        }

        return $this;
    }

    /**
     * 由於有進行 to-Many 的 fetch，因此原本的 setFirstResults 和 setMaxResults 會出現問題，
     * 必須改為使用 Paginator
     */
    public function paginate()
    {
        /**
         * Starting with version 2.2 Doctrine ships with a Paginator for DQL queries,
         * Much more ez way to get count of query result
         * 
         * @var object
         */
        $this->paginator = new Paginator($this->qb, $fetchJoinCollection = true);

        return $this;
    }

    /**
     * query 重設
     */
    public function reset()
    {
        $this->qb = $this->em->createQueryBuilder();

        return $this;
    }

    /**
     * 印出 qb 物件實際執行時的 DQL
     * 
     * @return 1
     */
    public function _print()
    {
        return print($this->qb->getQuery()->getDql());
    }

    /**
     * 印出 condition
     */
    public function dump()
    {
        echo "<pre>"; print_r($this->condition); echo "</pre>";

        return $this;
    }

    /**
     * 取得 query 執行完的結果
     * 
     * @return array         
     */
    public function getResult()
    {
        $res = array();

        foreach ($this->paginator as $product) {
            $res[] = $product;
        }

        return $res;
    }

    /**
     * 不擴充 limit 的部份，返回整段 query 取得的結果數量，
     * 即專門用來取得總數用的
     * 
     * @return integer            
     */
    public function getCount()
    {
        return count($this->paginator); 
    }

    /**
     * Set condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;

        return $this;
    }

    public function addExtraCondition()
    {
        $this->qb->andWhere();
    }

    /**
     * 初始化 query 
     */
    protected function init()
    {
        $this->qb
            ->select(self::GOODS_ALIAS)
            ->from('WoojinGoodsBundle:GoodsPassport', self::GOODS_ALIAS)
            ->leftJoin(self::GOODS_ALIAS . '.categorys', self::CATEGORY_ALIAS)
            ->leftJoin(self::GOODS_ALIAS . '.orders', 'od')
            ->leftJoin(self::GOODS_ALIAS . '.brand', 'bd')
            ->leftJoin(self::GOODS_ALIAS . '.pattern', 'pt')
            ->leftJoin(self::GOODS_ALIAS . '.mt', 'mt')
            ->leftJoin('od.opes', 'op')
            ->leftJoin('op.user', 'u')
            ->leftJoin(self::GOODS_ALIAS . '.level', 'l')
        ;

        return $this;
    }

    /**
     * 擴展 query   
     */
    protected function extend($isCount = null, $isWeb = null)
    {
        $this->qbBind();

        if ($isWeb) {
            $this->qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->in('g.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY)),
                    $qb->expr()->andX(
                        $qb->expr()->eq('g.status', Avenue::GS_SOLDOUT),
                        $qb->expr()->eq('g.isBehalf', true) 
                    )
                )
            );
        }

        if (!$isCount) {
            $this
                ->setQbOrderBy()
                ->setQbLimit()
            ;
        }
        
        return $this; 
    }

    /**
     * setParameter
     */
    protected function setParameter()
    {
        return $this;
    }

    /**
     * query 綁定各種轉換後的條件
     */
    protected function qbBind()
    {
        return $this
            ->qbBindEntity('status')
            ->qbBindEntity('mt')
            ->qbBindEntity('pattern')
            ->qbBindEntity('color')
            ->qbBindEntity('brand')
            ->qbBindEntity('source')
            ->qbBindEntity('promotion')
            ->qbBindEntity('activity')
            ->qbBindLevel()
            ->qbBindCategory()
            ->qbBindStore()
            ->qbBindIsAllowWeb()
            ->qbBindPrice()
            ->qbBindName()
        ;
    }

    /**
     * 設置 query 的 擷取範圍，用來實作分頁的商務邏輯
     */
    protected function setQbLimit()
    {
        if (!$this->access('perPage')) {
            $this->condition->perPage = self::PER_PAGE;
        }

        if (!$this->access('page')) {
            $this->condition->page = 1;
        }

        $this->qb
            ->setFirstResult(($this->condition->page - 1) * $this->condition->perPage)
            ->setMaxResults($this->condition->perPage)
        ;

        return $this;
    }

    /**
     * 設置 query 的排序依據
     */
    protected function setQbOrderBy()
    {
        if (!($orderBy = $this->access('orderBy'))) {
            return $this;
        }

        if (!$this->access('sort', $orderBy)) {
            return $this;
        }

        if (!$this->access('order', $orderBy)) {
            $this->condition->order = 'ASC';
        }

        $this->qb->orderBy($this->condition->orderBy->sort, $this->condition->orderBy->order);

        return $this;
    }

    /**
     * 取得 $this->condition 的屬性的方法
     * 
     * @param  string $attribute
     * @param  object
     * @return attribute | boolean
     */
    protected function access($attribute, $object = null)
    {
        if (!$object) {
            $object = $this->condition;
        }

        return (property_exists($object, $attribute)) ? $object->$attribute : false;
    }

    private function qbBindCategory()
    {
        if ($gd = $this->access(self::GOODS_ALIAS)) {
            if (!$this->access('category', $gd)) {
                return $this;
            }

            if (empty($gd->category)) {
                return $this;
            }

            $this->qb->andWhere($this->qb->expr()->in(self::CATEGORY_ALIAS . '.id', ':ids'));
            $this->qb->setParameter('ids', $gd->category->id);
        }


        return $this;
    }

    private function qbBindName()
    {
        if (!$this->access('name')) {
            return $this;
        }

        if (empty($this->condition->name)) {
            return $this;
        }

        $names = explode(' ', $this->condition->name);

        $or = $this->qb->expr()->orX();

        foreach ($names as $key => $name) {
            $or
                ->add($this->qb->expr()->eq(self::GOODS_ALIAS . '.model', $this->qb->expr()->literal($name)))
                ->add($this->qb->expr()->like(self::GOODS_ALIAS . '.name', $this->qb->expr()->literal('%' . $name . '%')))
                ->add($this->qb->expr()->like('bd.name', $this->qb->expr()->literal('%' . $name . '%')))
                ->add($this->qb->expr()->like('pt.name', $this->qb->expr()->literal('%' . $name . '%')))
                ->add($this->qb->expr()->like('mt.name', $this->qb->expr()->literal('%' . $name . '%')))
            ;
        }

        $this->qb->andWhere($or);

        return $this;
    }

    private function qbBindIsAllowWeb()
    {
        if ($this->access('isWeb') && $this->condition->isWeb) {
            $this->qb->andWhere(
                $this->qb->expr()->andX(
                    $this->qb->expr()->orX(
                        $this->qb->expr()->in(self::GOODS_ALIAS . '.status', array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY)),
                        $this->qb->expr()->andX(
                            $this->qb->expr()->in(self::GOODS_ALIAS . '.status', array(Avenue::GS_SOLDOUT, Avenue::GS_BEHALF)),
                            $this->qb->expr()->eq(self::GOODS_ALIAS . '.isBehalf', true)
                        )
                    ),
                    $this->qb->expr()->eq(self::GOODS_ALIAS . '.isAllowWeb', true)
                )
            );
        }

        return $this;
    }

    private function qbBindPrice()
    {
        if ($this->access('upperPrice')) {
            $this->qb->andWhere($this->qb->expr()->lte(self::GOODS_ALIAS . '.price', ':upperPrice'));
            $this->qb->setParameter(':upperPrice', empty($this->condition->upperPrice) ? 10000000 : $this->condition->upperPrice);
        }

        if ($this->access('lowerPrice')) {
            $this->qb->andWhere($this->qb->expr()->gte(self::GOODS_ALIAS . '.price', ':lowerPrice'));
            $this->qb->setParameter(':lowerPrice', empty($this->condition->upperPrice) ? 10000000 : $this->condition->upperPrice);
        }

        return $this;
    }

    private function qbBindEntity($attribute)
    {
        if (($gd = $this->access(self::GOODS_ALIAS)) 
            && ($entitys = $this->access($attribute, $gd)) 
            && is_array($entitys) 
            && !empty($entitys)
        ) {
            $ids = array();
            
            foreach ($entitys as $entity) {
                $ids[] = $entity->id; 
            }

            $this->qb->andWhere(
                $this->qb->expr()->in(self::GOODS_ALIAS . '.' . $attribute, ':' . $attribute)
            );

            $this->qb->setParameter($attribute, $ids);
        }

        return $this;
    }

    private function qbBindLevel()
    {
        if (($gd = $this->access(self::GOODS_ALIAS)) 
            && ($entitys = $this->access('level', $gd)) 
            && is_array($entitys) 
            && !empty($entitys)
        ) {
            $ids = array();
            
            foreach ($entitys as $entity) {
                if ($entity->id == 100) {// 100 是用來實作二手商品為 1 ~ 23 所定義的特例
                    for ($i = 1; $i <= 21; $i ++) {
                        $ids[] = $i;
                    }
                } else {
                     $ids[] = $entity->id;
                }
            }

            $this->qb->andWhere(
                $this->qb->expr()->in(self::GOODS_ALIAS . '.level', ':' . 'level')
            );

            $this->qb->setParameter('level', $ids);
        }

        return $this;
    }

    private function qbBindStore()
    {
        if (($gd = $this->access(self::GOODS_ALIAS)) 
            && ($entitys = $this->access('store', $gd)) 
            && is_array($entitys)
        ) {
            $sns = array();
            
            foreach ($entitys as $entity) {
                $sns[] = $entity->sn; 
            }

            $this->qb->andWhere(
                $this->qb->expr()->in($this->qb->expr()->substring(self::GOODS_ALIAS . '.sn', 1, 1), ':sn')
            );

            $this->qb->setParameter('sn', $sns);
        }

        return $this;
    }
}
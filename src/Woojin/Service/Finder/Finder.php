<?php

namespace Woojin\Service\Finder;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

abstract class Finder
{
    protected $em;
    protected $qb;
    protected $page;
    protected $perpage;
    protected $count;
    protected $data;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;

        $this->init();
    }

    protected function initQbSelect(){}

    /**
     * 這是一個和 view, parameter 以及 request 耦合的
     *  
     * @param  Request $request 
     * @return $this
     */
    public function find(Request $request)
    {
        $this
            ->handleBrand($request->request->get('brand', array()))
            ->handlePattern($request->request->get('pattern', array()))
            ->handlePatternGroup($request->request->get('patternGroup', array()))
            ->handleMt($request->request->get('mt', array()))
            ->handleColor($request->request->get('color', array()))
            ->handleLevel($request->request->get('level', array()))
            ->handleProductStatus($request->request->get('productStatus', array()))
            ->handlePromotion($request->request->get('promotion', array()))
            ->handleActivity($request->request->get('activity', array()))
            ->handleIsAllowWeb($request->request->get('isAllowWeb'))
            ->handleIsYahoo($request->request->get('isYahoo'))
            ->handleStore($request->request->get('store', array()))
            ->handleTextSeries($request->request->get('textSeries'))
            ->handleOrderStatus($request->request->get('orderStatus', array()))
            ->handleOrderKind($request->request->get('orderKind', array()))
            ->handleOpeDatetime($request->request->get('startAt'), $request->request->get('endAt'))
            ->handleCustomMobil($request->request->get('customMobil'))
            ->handleCustomMobilSold($request->request->get('customMobilSold'))
            ->handlePrice($request->request->get('price_start'), $request->request->get('price_end'))
            ->handlePage($request->request->get('page', 1)) //page
            ->handlePerpage($request->request->get('perpage', 50))
            ->handleOrderBy($request->request->get('order', 'g.id'), $request->request->get('dir', 'DESC'))
            ->exec()
        ;

        return $this;
    }

    public function getMobileViewData()
    {
        return array(
            'products' => $this->getData(),
            'count' => $this->getCount(),
            'perpage' => $this->getPerpage(),
            'page' => $this->getPage()
        );
    }

    public function init()
    {
        $this->page = null;
        $this->perpage = null;
        $this->count = null;
        $this->data = null;

        $this->initQb()->initQbSelect();

        return $this;
    }

    protected function initQb()
    {
        $this->qb = $this->newQb();

        return $this;
    }

    protected function newQb()
    {
        return $this->em->createQueryBuilder();
    }

    public function handleBrand(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('g.brand', $ids)
        );

        return $this;
    }

    public function handlePattern(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('g.pattern', $ids)
        );

        return $this;
    }

    public function handlePatternGroup(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('p.groupId', $ids)
        );

        return $this;
    }

    public function handleMt(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('g.mt', $ids)
        );

        return $this;
    }

    public function handleColor(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('g.color', $ids)
        );

        return $this;
    }

    public function handleLevel(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('g.level', $ids)
        );

        return $this;
    }

    public function handleProductStatus(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('g.status', $ids)
        );

        return $this;
    }

    public function handleActivity(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('g.activity', $ids)
        );

        return $this;
    }

    public function handlePromotion(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('g.promotion', $ids)
        );

        return $this;
    }

    public function handleIsAllowWeb($bool)
    {
        if (NULL === $bool || '' === $bool) {
            return $this;
        }

        $this->qb->andWhere(
            (1 === (int) $bool) ? $this->qb->expr()->eq('g.isAllowWeb', true) : $this->qb->expr()->eq('g.isAllowWeb', 0)
        );

        return $this;
    }

    public function handleIsYahoo($bool)
    {
        if (NULL === $bool || '' === $bool) {
            return $this;
        }

        $this->qb->andWhere(
            (1 === (int) $bool) ? $this->qb->expr()->isNotNull('g.yahooId') : $this->qb->expr()->isNull('g.yahooId')
        );

        return $this;
    }

    public function handleStore(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $sns = $this->getStoreSns($ids);

        $this->qb->andWhere(
            $this->qb->expr()->in($this->qb->expr()->substring('g.sn', 1, 1), $sns)
        );

        return $this;
    }

    public function handleTextSeries($text)
    {
        if (empty($text)) {
            return $this;
        }

        $values = explode(' ', $text);

        $or = $this->qb->expr()->orX();

        // 品名,序號,型號,產編,內碼
        foreach ($values as $key => $val) {
            if (!is_string($val)) {
                continue;
            }

            $or
                ->add($this->qb->expr()->eq('g.org_sn', $this->qb->expr()->literal($val)))
                ->add($this->qb->expr()->eq('g.sn', $this->qb->expr()->literal($val)))
                ->add($this->qb->expr()->eq('g.model', $this->qb->expr()->literal($val)))
                ->add($this->qb->expr()->eq('g.customSn', $this->qb->expr()->literal($val)))
                ->add($this->qb->expr()->like('g.name', $this->qb->expr()->literal('%' . $val . '%')))
                ->add($this->qb->expr()->eq('b.name', $this->qb->expr()->literal($val)))
            ;
        }

        $this->qb->andWhere($or);

        return $this;
    }

    public function handleOrderStatus(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('o.status', $ids)
        );

        return $this;
    }

    public function handleOrderKind(array $ids)
    {
        if ($this->isEmptyIds($ids)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->in('o.kind', $ids)
        );

        return $this;
    }

    public function handleOpeDatetime($startAt, $stopAt)
    {
        if (empty($startAt) || empty($stopAt)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->between(
                'e.datetime', 
                $this->qb->expr()->literal($startAt), 
                $this->qb->expr()->literal($stopAt)
            )
        );

        return $this;
    }

    public function handleCustomMobil($mobil)
    {
        if (empty($mobil)) {
            return $this;
        }

        $this->qb->andWhere(
            $this->qb->expr()->eq('c.mobil', $this->qb->expr()->literal($mobil))
        );

        return $this;
    }

    public function handleCustomMobilSold($mobil)
    {
        if (empty($mobil)) {
            return $this;
        }

        $this->qb->andWhere($this->qb->expr()->eq('co.mobil', $this->qb->expr()->literal($mobil)));

        return $this;
    }

    public function handlePrice($start, $end)
    {
        if (empty($start) && empty($end)) {
            return $this;
        }

        $and = $this->qb->expr()->andX();

        if (!empty($start)) {
            $and->add($this->qb->expr()->gte('g.price', $start));
        }

        if (!empty($end)) {
            $and->add($this->qb->expr()->lte('g.price', $end));
        }

        $this->qb->andWhere($and);

        return $this;
    }

    public function handleOrderBy($order = 'id', $dir = 'DESC')
    {
        $this->qb->orderBy($order, $dir);

        return $this;
    }

    public function handlePage($page)
    {
        $this->page = $page;

        return $this;
    }

    public function handlePerpage($perpage)
    {
        $this->perpage = $perpage;

        return $this;
    }

    public function exec()
    {
        $this->qb
            ->setFirstResult(($this->page - 1) * $this->perpage)
            ->setMaxResults($this->perpage)
        ;

        $paginator = new Paginator($this->qb, $fetchJoinCollection = true);
        
        $this->count = count($paginator);
        $this->pushData($paginator);
        $paginator = null;
        $this->qb = null;

        return $this;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getPerpage()
    {
        return $this->perpage;
    }

    public function getData()
    {
        return $this->data;
    }

    protected function pushData(Paginator $paginator)
    {
        $this->data = array();

        foreach ($paginator as $product) {
            $this->data[] = $product;
        }

        return $this;
    }

    protected function getStoreSns(array $ids)
    {
        $sns = array();

        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('s')
            ->from('WoojinStoreBundle:Store', 's')
            ->where($qb->expr()->in('s.id', $ids))
        ;

        foreach ($qb->getQuery()->getResult() as $store) {
            $sns[] = $store->getSn();
        }

        return $sns;
    }

    protected function isEmptyIds(array $ids)
    {
        return (empty($ids) || empty($ids[0]));
    }
}
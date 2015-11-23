<?php

namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StockTakeRecord
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class StockTakeRecord
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="do_at", type="datetime")
     */
    private $doAt;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\GoodsBundle\Entity\GoodsPassport", inversedBy="stock_take_record")
     * @var GoodsPassport
     */
    protected $goods_passport;

    /**
     * @ORM\ManyToOne(targetEntity="Activity", inversedBy="stock_take_record")
     * @var Activity
     */
    protected $activity;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="stock_take_record")
     * @var User
     */
    protected $user;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set doAt
     *
     * @param \DateTime $doAt
     * @return StockTakeRecord
     */
    public function setDoAt($doAt)
    {
        $this->doAt = $doAt;
    
        return $this;
    }

    /**
     * Get doAt
     *
     * @return \DateTime 
     */
    public function getDoAt()
    {
        return $this->doAt;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return StockTakeRecord
     */
    public function setAction($action)
    {
        $this->action = $action;
    
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set goods_passport
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport
     * @return StockTakeRecord
     */
    public function setGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport = null)
    {
        $this->goods_passport = $goodsPassport;
    
        return $this;
    }

    /**
     * Get goods_passport
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport 
     */
    public function getGoodsPassport()
    {
        return $this->goods_passport;
    }

    /**
     * Set activity
     *
     * @param \Woojin\StoreBundle\Entity\Activity $activity
     * @return StockTakeRecord
     */
    public function setActivity(\Woojin\StoreBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;
    
        return $this;
    }

    /**
     * Get activity
     *
     * @return \Woojin\StoreBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return StockTakeRecord
     */
    public function setUser(\Woojin\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Woojin\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
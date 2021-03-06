<?php 
namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table(name="ope",indexes={@ORM\Index(name="datetime_idx", columns={"datetime"})})
 * @ORM\HasLifecycleCallbacks()
 */
class Ope
{
    /**
     * @Exclude
     * @ORM\ManyToOne(targetEntity="PayType", inversedBy="opes")
     * @var PayType
     */
    protected $payType;

    /**
     * @Exclude
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="opes", cascade={"persist"})
     * @var Orders
     */
    protected $orders;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="opes")
     * @var User
     */
    protected $user;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $act;

    /**
     * @ORM\Column(type="datetime", length=50)
     */
    protected $datetime;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $memo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $amount;

    /**
     * @ORM\PrePersist
     */
    public function autoSetDatetime()
    {
        $this->setDatetime(new \Datetime());
    }

    /**
     * An Alias of Set orders
     *
     * @param \Woojin\OrderBundle\Entity\Orders $orders
     * @return Ope
     */
    public function setOrder(\Woojin\OrderBundle\Entity\Orders $orders = null)
    {
        $this->orders = $orders;
    
        return $this;
    }

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
     * Set act
     *
     * @param string $act
     * @return Ope
     */
    public function setAct($act)
    {
        $this->act = $act;
    
        return $this;
    }

    /**
     * Get act
     *
     * @return string 
     */
    public function getAct()
    {
        return $this->act;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return Ope
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    
        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return Ope
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;
    
        return $this;
    }

    /**
     * Get memo
     *
     * @return string 
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * Set orders
     *
     * @param \Woojin\OrderBundle\Entity\Orders $orders
     * @return Ope
     */
    public function setOrders(\Woojin\OrderBundle\Entity\Orders $orders = null)
    {
        $this->orders = $orders;
    
        return $this;
    }

    /**
     * Get orders
     *
     * @return \Woojin\OrderBundle\Entity\Orders 
     */
    public function getOrder()
    {
        return $this->orders;
    }

    /**
     * Get orders
     *
     * @return \Woojin\OrderBundle\Entity\Orders 
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return Ope
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

    /**
     * Set payType
     *
     * @param \Woojin\OrderBundle\Entity\PayType $payType
     * @return Ope
     */
    public function setPayType(\Woojin\OrderBundle\Entity\PayType $payType = null)
    {
        $this->payType = $payType;
    
        return $this;
    }

    /**
     * Get payType
     *
     * @return \Woojin\OrderBundle\Entity\PayType 
     */
    public function getPayType()
    {
        return $this->payType;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return Ope
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }
}

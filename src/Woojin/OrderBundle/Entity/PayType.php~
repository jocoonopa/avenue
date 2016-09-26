<?php 

namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table(name="pay_type")
 */
class PayType
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Ope", mappedBy="payType")
     * @var Opes[]
     */
    protected $opes;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Orders", mappedBy="pay_type")
     * @var Orders[]
     */
    protected $orders;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $discount;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return PayType
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set discount
     *
     * @param string $discount
     * @return PayType
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    
        return $this;
    }

    /**
     * Get discount
     *
     * @return string 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Add orders
     *
     * @param \Woojin\OrderBundle\Entity\Orders $orders
     * @return PayType
     */
    public function addOrder(\Woojin\OrderBundle\Entity\Orders $orders)
    {
        $this->orders[] = $orders;
    
        return $this;
    }

    /**
     * Remove orders
     *
     * @param \Woojin\OrderBundle\Entity\Orders $orders
     */
    public function removeOrder(\Woojin\OrderBundle\Entity\Orders $orders)
    {
        $this->orders->removeElement($orders);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Add opes
     *
     * @param \Woojin\OrderBundle\Entity\Ope $opes
     * @return PayType
     */
    public function addOpe(\Woojin\OrderBundle\Entity\Ope $opes)
    {
        $this->opes[] = $opes;
    
        return $this;
    }

    /**
     * Remove opes
     *
     * @param \Woojin\OrderBundle\Entity\Ope $opes
     */
    public function removeOpe(\Woojin\OrderBundle\Entity\Ope $opes)
    {
        $this->opes->removeElement($opes);
    }

    /**
     * Get opes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOpes()
    {
        return $this->opes;
    }
}

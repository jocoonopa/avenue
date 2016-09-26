<?php

namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BenefitFrag
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class BenefitFrag
{
    /**
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="fits")
     * @var order
     */
    protected $order;

    /**
     * @ORM\ManyToOne(targetEntity="Benefit", inversedBy="frags")
     * @var From
     */
    protected $from;

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
     * @ORM\Column(name="split_at", type="datetime")
     */
    private $splitAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;


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
     * Set splitAt
     *
     * @param \DateTime $splitAt
     * @return BenefitFrag
     */
    public function setSplitAt($splitAt)
    {
        $this->splitAt = $splitAt;
    
        return $this;
    }

    /**
     * Get splitAt
     *
     * @return \DateTime 
     */
    public function getSplitAt()
    {
        return $this->splitAt;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     * @return BenefitFrag
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

    /**
     * Set order
     *
     * @param \Woojin\OrderBundle\Entity\Orders $order
     * @return BenefitFrag
     */
    public function setOrder(\Woojin\OrderBundle\Entity\Orders $order = null)
    {
        $this->order = $order;
    
        return $this;
    }

    /**
     * Get order
     *
     * @return \Woojin\OrderBundle\Entity\Orders 
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set from
     *
     * @param \Woojin\OrderBundle\Entity\Benefit $from
     * @return BenefitFrag
     */
    public function setFrom(\Woojin\OrderBundle\Entity\Benefit $from = null)
    {
        $this->from = $from;
    
        return $this;
    }

    /**
     * Get from
     *
     * @return \Woojin\OrderBundle\Entity\Benefit 
     */
    public function getFrom()
    {
        return $this->from;
    }
}

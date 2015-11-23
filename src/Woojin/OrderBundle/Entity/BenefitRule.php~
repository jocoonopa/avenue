<?php

namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BenefitRule
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class BenefitRule
{
    /**
     * @ORM\ManyToOne(targetEntity="BenefitEvent", inversedBy="rules")
     * @var event
     */
    protected $event;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="sill", type="integer")
     */
    private $sill;

    /**
     * @var integer
     *
     * @ORM\Column(name="ceiling", type="integer")
     */
    private $ceiling;

    /**
     * @var integer
     *
     * @ORM\Column(name="gift", type="integer")
     */
    private $gift;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_stack", type="boolean")
     */
    private $isStack;


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
     * Set sill
     *
     * @param integer $sill
     * @return BenefitRule
     */
    public function setSill($sill)
    {
        $this->sill = $sill;
    
        return $this;
    }

    /**
     * Get sill
     *
     * @return integer 
     */
    public function getSill()
    {
        return $this->sill;
    }

    /**
     * Set gift
     *
     * @param integer $gift
     * @return BenefitRule
     */
    public function setGift($gift)
    {
        $this->gift = $gift;
    
        return $this;
    }

    /**
     * Get gift
     *
     * @return integer 
     */
    public function getGift()
    {
        return $this->gift;
    }

    /**
     * Set isStack
     *
     * @param boolean $isStack
     * @return BenefitRule
     */
    public function setIsStack($isStack)
    {
        $this->isStack = $isStack;
    
        return $this;
    }

    /**
     * Get isStack
     *
     * @return boolean 
     */
    public function getIsStack()
    {
        return $this->isStack;
    }

    /**
     * Set event
     *
     * @param \Woojin\OrderBundle\Entity\BenefitEvent $event
     * @return BenefitRule
     */
    public function setEvent(\Woojin\OrderBundle\Entity\BenefitEvent $event = null)
    {
        $this->event = $event;
    
        return $this;
    }

    /**
     * Get event
     *
     * @return \Woojin\OrderBundle\Entity\BenefitEvent 
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set ceiling
     *
     * @param integer $ceiling
     * @return BenefitRule
     */
    public function setCeiling($ceiling)
    {
        $this->ceiling = $ceiling;
    
        return $this;
    }

    /**
     * Get ceiling
     *
     * @return integer 
     */
    public function getCeiling()
    {
        return $this->ceiling;
    }
}
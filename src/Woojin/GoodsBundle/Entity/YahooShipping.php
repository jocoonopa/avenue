<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * YahooShipping
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class YahooShipping
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_default_on", type="boolean")
     */
    private $isDefaultOn;

    /**
     * @var string
     *
     * @ORM\Column(name="fee_type", type="string", length=50)
     */
    private $feeType;


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
     * @return YahooShipping
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
     * Set isDefaultOn
     *
     * @param boolean $isDefaultOn
     * @return YahooShipping
     */
    public function setIsDefaultOn($isDefaultOn)
    {
        $this->isDefaultOn = $isDefaultOn;
    
        return $this;
    }

    /**
     * Get isDefaultOn
     *
     * @return boolean 
     */
    public function getIsDefaultOn()
    {
        return $this->isDefaultOn;
    }

    /**
     * Set feeType
     *
     * @param string $feeType
     * @return YahooShipping
     */
    public function setFeeType($feeType)
    {
        $this->feeType = $feeType;
    
        return $this;
    }

    /**
     * Get feeType
     *
     * @return string 
     */
    public function getFeeType()
    {
        return $this->feeType;
    }
}
<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * YahooCategory
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class YahooCategory
{
    /**
     * @ORM\OneToOne(targetEntity="Brand", inversedBy="yc")
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="id")
     * @var Brand
     */
    protected $brand;

    /**
     * @ORM\OneToOne(targetEntity="Pattern", inversedBy="yc")
     * @ORM\JoinColumn(name="pattern_id", referencedColumnName="id")
     * @var Pattern
     */
    protected $pattern;

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
     * @var integer
     *
     * @ORM\Column(name="yahoo_id", type="integer")
     */
    private $yahooId;


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
     * @return YahooCategory
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
     * Set yahooId
     *
     * @param integer $yahooId
     * @return YahooCategory
     */
    public function setYahooId($yahooId)
    {
        $this->yahooId = $yahooId;
    
        return $this;
    }

    /**
     * Get yahooId
     *
     * @return integer 
     */
    public function getYahooId()
    {
        return $this->yahooId;
    }

    /**
     * Set brand
     *
     * @param \Woojin\GoodsBundle\Entity\Brand $brand
     * @return YahooCategory
     */
    public function setBrand(\Woojin\GoodsBundle\Entity\Brand $brand = null)
    {
        $this->brand = $brand;
    
        return $this;
    }

    /**
     * Get brand
     *
     * @return \Woojin\GoodsBundle\Entity\Brand 
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set pattern
     *
     * @param \Woojin\GoodsBundle\Entity\Pattern $pattern
     * @return YahooCategory
     */
    public function setPattern(\Woojin\GoodsBundle\Entity\Pattern $pattern = null)
    {
        $this->pattern = $pattern;
    
        return $this;
    }

    /**
     * Get pattern
     *
     * @return \Woojin\GoodsBundle\Entity\Pattern 
     */
    public function getPattern()
    {
        return $this->pattern;
    }
}

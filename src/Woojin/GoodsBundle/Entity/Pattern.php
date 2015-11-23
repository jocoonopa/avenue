<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Pattern
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\GoodsBundle\Entity\PatternRepository")
 */
class Pattern
{
    /**
     * @Exclude
     * @ORM\OneToOne(targetEntity="YahooCategory", mappedBy="pattern")
     **/
    private $yc;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="pattern")
     * @var GoodsPassports[]
     */
    protected $goodsPassports;

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
     * @ORM\Column(name="name", type="string", length=50)
     */
    private $name;

    /**
     * @var string
     * 
     * @ORM\Column(name="uitox_name", type="string", length=50, nullable=true)
     */
    private $uitoxName;

    /**
     * @var integer
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    private $groupId;

    /**
     * @ORM\Column(type="integer")
     */
    protected $count;

    /**
     * @ORM\Column(type="integer")
     */
    protected $womenCount;

    /**
     * @ORM\Column(type="integer")
     */
    protected $menCount;

    /**
     * @ORM\Column(type="integer")
     */
    protected $secondhandCount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->goodsPassports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->count = 0;
        $this->womenCount = 0;
        $this->menCount = 0;
        $this->secondhandCount = 0;
    }

    public function __toString()
    {
        return $this->name;
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
     * @return Pattern
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
     * Add goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     * @return Pattern
     */
    public function addGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports)
    {
        $this->goodsPassports[] = $goodsPassports;
    
        return $this;
    }

    /**
     * Remove goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     */
    public function removeGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports)
    {
        $this->goodsPassports->removeElement($goodsPassports);
    }

    /**
     * Get goodsPassports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoodsPassports()
    {
        return $this->goodsPassports;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return Pattern
     */
    public function setCount($count)
    {
        $this->count = $count;
    
        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set womenCount
     *
     * @param integer $womenCount
     * @return Pattern
     */
    public function setWomenCount($womenCount)
    {
        $this->womenCount = $womenCount;
    
        return $this;
    }

    /**
     * Get womenCount
     *
     * @return integer 
     */
    public function getWomenCount()
    {
        return $this->womenCount;
    }

    /**
     * Set menCount
     *
     * @param integer $menCount
     * @return Pattern
     */
    public function setMenCount($menCount)
    {
        $this->menCount = $menCount;
    
        return $this;
    }

    /**
     * Get menCount
     *
     * @return integer 
     */
    public function getMenCount()
    {
        return $this->menCount;
    }

    /**
     * Set secondhandCount
     *
     * @param integer $secondhandCount
     * @return Pattern
     */
    public function setSecondhandCount($secondhandCount)
    {
        $this->secondhandCount = $secondhandCount;
    
        return $this;
    }

    /**
     * Get secondhandCount
     *
     * @return integer 
     */
    public function getSecondhandCount()
    {
        return $this->secondhandCount;
    }

    /**
     * Set yc
     *
     * @param \Woojin\GoodsBundle\Entity\YahooCategory $yc
     * @return Pattern
     */
    public function setYc(\Woojin\GoodsBundle\Entity\YahooCategory $yc = null)
    {
        $this->yc = $yc;
    
        return $this;
    }

    /**
     * Get yc
     *
     * @return \Woojin\GoodsBundle\Entity\YahooCategory 
     */
    public function getYc()
    {
        return $this->yc;
    }

    /**
     * Set uitoxName
     *
     * @param string $uitoxName
     * @return Pattern
     */
    public function setUitoxName($uitoxName)
    {
        $this->uitoxName = $uitoxName;
    
        return $this;
    }

    /**
     * Get uitoxName
     *
     * @return string 
     */
    public function getUitoxName()
    {
        return $this->uitoxName;
    }

    /**
     * Set groupId
     *
     * @param integer $groupId
     * @return Pattern
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    
        return $this;
    }

    /**
     * Get groupId
     *
     * @return integer 
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}
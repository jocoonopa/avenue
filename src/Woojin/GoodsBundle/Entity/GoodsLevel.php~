<?php 

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

use Woojin\Utility\Avenue\Avenue;

/**
 * @ORM\Entity(repositoryClass="Woojin\GoodsBundle\Entity\GoodsLevelRepository")
 * @ORM\Table(name="goods_level")
 */
class GoodsLevel
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="level")
     * @var GoodsPassports[]
     */
    protected $goods_passports;

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
        $this->goods_passports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->count = 0;
        $this->womenCount = 0;
        $this->menCount = 0;
        $this->secondhandCount = 0;
    }

    public function isNew()
    {
        return in_array($this->id, array(Avenue::GL_NEW, Avenue::GL_DEMO));
    }

    public function getConvertName($convertName = '二手商品')
    {
        return $this->isNew() ? $this->name : $convertName;
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
     * @return GoodsLevel
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
     * Add goods_passports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     * @return GoodsLevel
     */
    public function addGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports)
    {
        $this->goods_passports[] = $goodsPassports;
    
        return $this;
    }

    /**
     * Remove goods_passports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     */
    public function removeGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports)
    {
        $this->goods_passports->removeElement($goodsPassports);
    }

    /**
     * Get goods_passports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoodsPassports()
    {
        return $this->goods_passports;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return GoodsLevel
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
     * @return GoodsLevel
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
     * @return GoodsLevel
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
     * @return GoodsLevel
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
}

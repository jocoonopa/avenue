<?php 

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity(repositoryClass="Woojin\GoodsBundle\Entity\BrandRepository")
 * @ORM\Table(name="brand")
 */
class Brand
{
    /**
     * @ORM\Column(type="float")
     */
    private $ratio;

    /**
     * @Exclude
     * @ORM\OneToOne(targetEntity="YahooCategory", mappedBy="brand")
     **/
    private $yc;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="brand")
     * 
     * @var GoodsPassports[]
     */
    protected $goodsPassports;

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
     * @return Brand
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
     * @return Brand
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
     * @return Brand
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
     * @return Brand
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
     * @return Brand
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
     * @return Brand
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
     * @return Brand
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
     * Set ratio
     *
     * @param integer $ratio
     * @return Brand
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;
    
        return $this;
    }

    /**
     * Get ratio
     *
     * @return integer 
     */
    public function getRatio()
    {
        return $this->ratio;
    }
}

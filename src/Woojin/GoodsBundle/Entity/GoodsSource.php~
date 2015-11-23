<?php 
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table(name="goods_source")
 */
class GoodsSource
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="source")
     * @var GoodsPassport[]
     */
    protected $goods_passport;

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
     * Constructor
     */
    public function __construct()
    {
        $this->goods_passport = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return GoodsSource
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
     * Add goods_passport
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport
     * @return GoodsSource
     */
    public function addGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport)
    {
        $this->goods_passport[] = $goodsPassport;
    
        return $this;
    }

    /**
     * Remove goods_passport
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport
     */
    public function removeGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport)
    {
        $this->goods_passport->removeElement($goodsPassport);
    }

    /**
     * Get goods_passport
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoodsPassport()
    {
        return $this->goods_passport;
    }

    public function __toString()
    {
        return $this->name;
    }
}
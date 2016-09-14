<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Color
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Color
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="color")
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
     * @ORM\Column(name="name", type="string", length=70)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20)
     */
    private $code;


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
     * @return Color
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
     * Set code
     *
     * @param string $code
     * @return Color
     */
    public function setCode($code)
    {
        $this->code = $code;
    
        return $this;
    }

    /**
     * Get code
     *
     * @return string 
     */
    public function getCode()
    {
        return $this->code;
    }
    /**
     * Constructor
     */
    public function __construct($name = null, $code = null)
    {
        $this->goodsPassports = new \Doctrine\Common\Collections\ArrayCollection();

        $this->name = $name;

        $this->code = $code;
    }
    
    /**
     * Add goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     * @return Color
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

    public function __toString()
    {
        return $this->name;
    }
}

<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * SeoSlogan
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SeoSlogan
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="seoSlogan")
     * @var products[]
     */
    protected $products;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="seoSlogan2")
     * @var products2[]
     */
    protected $products2;

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
     * @return SeoSlogan
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
     * Constructor
     */
    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add products
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $products
     * @return SeoSlogan
     */
    public function addProduct(\Woojin\GoodsBundle\Entity\GoodsPassport $products)
    {
        $this->products[] = $products;
    
        return $this;
    }

    /**
     * Remove products
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $products
     */
    public function removeProduct(\Woojin\GoodsBundle\Entity\GoodsPassport $products)
    {
        $this->products->removeElement($products);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add products2
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $products2
     * @return SeoSlogan
     */
    public function addProducts2(\Woojin\GoodsBundle\Entity\GoodsPassport $products2)
    {
        $this->products2[] = $products2;
    
        return $this;
    }

    /**
     * Remove products2
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $products2
     */
    public function removeProducts2(\Woojin\GoodsBundle\Entity\GoodsPassport $products2)
    {
        $this->products2->removeElement($products2);
    }

    /**
     * Get products2
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts2()
    {
        return $this->products2;
    }
}
<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductTl
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\GoodsBundle\Entity\ProductTlRepository")
 */
class ProductTl
{
    /**
     * @ORM\OneToOne(targetEntity="GoodsPassport", mappedBy="productTl")
     **/
    private $product;

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
     * @ORM\Column(name="end_at", type="datetime")
     */
    private $endAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="price", type="integer")
     */
    private $price;

    /**
     * 搶購結束時間 - 現在時間
     *
     * >= 0 表示搶購尚未結束
     * 
     * @return integer
     */
    public function isValid()
    {
        $now = new \DateTime();
        $stopAt = $this->getEndAt();
        $interval = $now->diff($stopAt);

        return (int) $interval->format('%R%a') >= 0;
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
     * Set endAt
     *
     * @param \DateTime $endAt
     * @return ProductTl
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;
    
        return $this;
    }

    /**
     * Get endAt
     *
     * @return \DateTime 
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * Set product
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $product
     * @return ProductTl
     */
    public function setProduct(\Woojin\GoodsBundle\Entity\GoodsPassport $product = null)
    {
        $this->product = $product;
    
        return $this;
    }

    /**
     * Get product
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport 
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return ProductTl
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return integer 
     */
    public function getPrice()
    {
        return $this->price;
    }
}

<?php

namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Activity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\StoreBundle\Entity\ActivityRepository")
 */
class Activity
{
  /**
   * @Exclude
   * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\GoodsPassport", mappedBy="activity")
   * @var GoodsPassport[]
   */
  protected $goods_passport;

  /**
   * @ORM\OneToMany(targetEntity="StockTakeRecord", mappedBy="activity")
   * @var StockTakeRecord[]
   */
  protected $stock_take_record;

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
   * @ORM\Column(name="name", type="string", length=255)
   */
  private $name;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="start_at", type="datetime")
   */
  private $startAt;

  /**
   * @var \DateTime
   *
   * @ORM\Column(name="end_at", type="datetime")
   */
  private $endAt;

  /**
   * @var string
   *
   * @ORM\Column(name="description", type="text")
   */
  private $description;

  /**
   * @var boolean
   *
   * @ORM\Column(name="is_hidden", type="boolean") 
   */
  private $isHidden;


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
   * @return Activity
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
   * Set startAt
   *
   * @param \DateTime $startAt
   * @return Activity
   */
  public function setStartAt($startAt)
  {
    $this->startAt = $startAt;
  
    return $this;
  }

  /**
   * Get startAt
   *
   * @return \DateTime 
   */
  public function getStartAt()
  {
    return $this->startAt;
  }

  /**
   * Set endAt
   *
   * @param \DateTime $endAt
   * @return Activity
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
   * Set description
   *
   * @param string $description
   * @return Activity
   */
  public function setDescription($description)
  {
    $this->description = $description;
  
    return $this;
  }

  /**
   * Get description
   *
   * @return string 
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * Add goods_passport
   *
   * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport
   * @return Activity
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
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->goods_passport = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stock_take_record = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add stock_take_record
     *
     * @param \Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord
     * @return Activity
     */
    public function addStockTakeRecord(\Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord)
    {
        $this->stock_take_record[] = $stockTakeRecord;
    
        return $this;
    }

    /**
     * Remove stock_take_record
     *
     * @param \Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord
     */
    public function removeStockTakeRecord(\Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord)
    {
        $this->stock_take_record->removeElement($stockTakeRecord);
    }

    /**
     * Get stock_take_record
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStockTakeRecord()
    {
        return $this->stock_take_record;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Set isHidden
     *
     * @param boolean $isHidden
     *
     * @return Activity
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * Get isHidden
     *
     * @return boolean
     */
    public function getIsHidden()
    {
        return $this->isHidden;
    }
}

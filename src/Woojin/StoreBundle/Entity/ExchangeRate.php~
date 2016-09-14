<?php

namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * ExchangeRate
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ExchangeRate
{
  /**
   * @Exclude
   * @ORM\OneToMany(targetEntity="\Woojin\StoreBundle\Entity\Store", mappedBy="exchange_rate")
   * @var Store[]
   */
  protected $stores;
  
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
   * @var float
   *
   * @ORM\Column(name="rate", type="float")
   */
  private $rate;

  /**
   * @var string
   *
   * @ORM\Column(name="symbol", type="string", length=255)
   */
  private $symbol;


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
   * @return ExchangeRate
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
   * Set rate
   *
   * @param float $rate
   * @return ExchangeRate
   */
  public function setRate($rate)
  {
    $this->rate = $rate;
  
    return $this;
  }

  /**
   * Get rate
   *
   * @return float 
   */
  public function getRate()
  {
    return $this->rate;
  }

  /**
   * Set symbol
   *
   * @param string $symbol
   * @return ExchangeRate
   */
  public function setSymbol($symbol)
  {
    $this->symbol = $symbol;
  
    return $this;
  }

  /**
   * Get symbol
   *
   * @return string 
   */
  public function getSymbol()
  {
    return $this->symbol;
  }
  
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->stores = new \Doctrine\Common\Collections\ArrayCollection();
  }
  
  /**
   * Add stores
   *
   * @param \Woojin\StoreBundle\Entity\Store $stores
   * @return ExchangeRate
   */
  public function addStore(\Woojin\StoreBundle\Entity\Store $stores)
  {
    $this->stores[] = $stores;
  
    return $this;
  }

  /**
   * Remove stores
   *
   * @param \Woojin\StoreBundle\Entity\Store $stores
   */
  public function removeStore(\Woojin\StoreBundle\Entity\Store $stores)
  {
    $this->stores->removeElement($stores);
  }

  /**
   * Get stores
   *
   * @return \Doctrine\Common\Collections\Collection 
   */
  public function getStores()
  {
    return $this->stores;
  }
}

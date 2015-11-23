<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AgencyCategory
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class AgencyCategory
{
  /**
   * @ORM\OneToMany(targetEntity="AgencyItem", mappedBy="agency_category")
   * @var AgencyItem[]
   */
  protected $agency_items;

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
   * @return AgencyCategory
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
      $this->agency_items = new \Doctrine\Common\Collections\ArrayCollection();
  }
  
  /**
   * Add agency_items
   *
   * @param \Woojin\AgencyBundle\Entity\AgencyItem $agencyItems
   * @return AgencyCategory
   */
  public function addAgencyItem(\Woojin\AgencyBundle\Entity\AgencyItem $agencyItems)
  {
    $this->agency_items[] = $agencyItems;

    return $this;
  }

  /**
   * Remove agency_items
   *
   * @param \Woojin\AgencyBundle\Entity\AgencyItem $agencyItems
   */
  public function removeAgencyItem(\Woojin\AgencyBundle\Entity\AgencyItem $agencyItems)
  {
      $this->agency_items->removeElement($agencyItems);
  }

  /**
   * Get agency_items
   *
   * @return \Doctrine\Common\Collections\Collection 
   */
  public function getAgencyItems()
  {
      return $this->agency_items;
  }

  public function __toString()
  {
    return $this->getName();
  }
}
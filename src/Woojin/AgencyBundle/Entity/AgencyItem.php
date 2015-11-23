<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AgencyItem
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class AgencyItem
{
  /**
   * @ORM\OneToMany(targetEntity="Object", mappedBy="agency_item")
   * @var Object[]
   */
  protected $objects;

  /**
   * @ORM\ManyToOne(targetEntity="AgencyCategory", inversedBy="agency_items")
   * @var AgencyCategory
   */
  protected $agency_category;

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
   * @return AgencyItem
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
      $this->objects = new \Doctrine\Common\Collections\ArrayCollection();
  }
  
  /**
   * Add objects
   *
   * @param \Woojin\AgencyBundle\Entity\Object $objects
   * @return AgencyItem
   */
  public function addObject(\Woojin\AgencyBundle\Entity\Object $objects)
  {
      $this->objects[] = $objects;
  
      return $this;
  }

  /**
   * Remove objects
   *
   * @param \Woojin\AgencyBundle\Entity\Object $objects
   */
  public function removeObject(\Woojin\AgencyBundle\Entity\Object $objects)
  {
      $this->objects->removeElement($objects);
  }

  /**
   * Get objects
   *
   * @return \Doctrine\Common\Collections\Collection 
   */
  public function getObjects()
  {
      return $this->objects;
  }

  /**
   * Set agency_category
   *
   * @param \Woojin\AgencyBundle\Entity\AgencyCategory $agencyCategory
   * @return AgencyItem
   */
  public function setAgencyCategory(\Woojin\AgencyBundle\Entity\AgencyCategory $agencyCategory = null)
  {
      $this->agency_category = $agencyCategory;
  
      return $this;
  }

  /**
   * Get agency_category
   *
   * @return \Woojin\AgencyBundle\Entity\AgencyCategory 
   */
  public function getAgencyCategory()
  {
      return $this->agency_category;
  }

  public function __toString()
  {
    return $this->getName();
  }
}
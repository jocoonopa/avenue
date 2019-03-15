<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObjectLocation
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ObjectLocation
{
  /**
   * @ORM\OneToMany(targetEntity="AgencyObject", mappedBy="object_location")
   * @var Object[]
   */
  protected $objects;

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
   * @return ObjectLocation
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
     * @param \Woojin\AgencyBundle\Entity\AgencyObject $objects
     * @return ObjectLocation
     */
    public function addObject(\Woojin\AgencyBundle\Entity\AgencyObject $objects)
    {
        $this->objects[] = $objects;
    
        return $this;
    }

    /**
     * Remove objects
     *
     * @param \Woojin\AgencyBundle\Entity\AgencyObject $objects
     */
    public function removeObject(\Woojin\AgencyBundle\Entity\AgencyObject $objects)
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

    public function __toString()
    {
        return $this->getName();
    }
}
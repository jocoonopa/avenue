<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OperationStatus
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class OperationStatus
{
  /**
   * @ORM\OneToMany(targetEntity="Operation", mappedBy="operation_status")
   * @var Operation[]
   */
  protected $operations;

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
   * @return OperationStatus
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
        $this->operations = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add operations
     *
     * @param \Woojin\AgencyBundle\Entity\Operation $operations
     * @return OperationStatus
     */
    public function addOperation(\Woojin\AgencyBundle\Entity\Operation $operations)
    {
        $this->operations[] = $operations;
    
        return $this;
    }

    /**
     * Remove operations
     *
     * @param \Woojin\AgencyBundle\Entity\Operation $operations
     */
    public function removeOperation(\Woojin\AgencyBundle\Entity\Operation $operations)
    {
        $this->operations->removeElement($operations);
    }

    /**
     * Get operations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOperations()
    {
        return $this->operations;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
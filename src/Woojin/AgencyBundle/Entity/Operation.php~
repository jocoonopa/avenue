<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Operation
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Operation
{
  /**
   * @ORM\ManyToOne(targetEntity="Object", inversedBy="operations")
   * @var Object
   */
  protected $object;

  /**
   * @ORM\ManyToOne(targetEntity="OperationKind", inversedBy="operations")
   * @var OperationKind
   */
  protected $operation_kind;

  /**
   * @ORM\ManyToOne(targetEntity="OperationStatus", inversedBy="operations")
   * @var OperationStatus
   */
  protected $operation_status;

  /**
   * @ORM\OneToMany(targetEntity="OperationRecord", mappedBy="operation")
   * @var OperationRecord[]
   */
  protected $operation_records;

  /**
   * @var integer
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var integer
   *
   * @ORM\Column(name="required", type="integer")
   */
  private $required;

  /**
   * @var integer
   *
   * @ORM\Column(name="paid", type="integer")
   */
  private $paid;

  /**
   * @var string
   *
   * @ORM\Column(name="memo", type="text")
   */
  private $memo;


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
   * Set required
   *
   * @param integer $required
   * @return Operation
   */
  public function setRequired($required)
  {
    $this->required = $required;
  
    return $this;
  }

  /**
   * Get required
   *
   * @return integer 
   */
  public function getRequired()
  {
    return $this->required;
  }

  /**
   * Set paid
   *
   * @param integer $paid
   * @return Operation
   */
  public function setPaid($paid)
  {
    $this->paid = $paid;
  
    return $this;
  }

  /**
   * Get paid
   *
   * @return integer 
   */
  public function getPaid()
  {
    return $this->paid;
  }

  /**
   * Set memo
   *
   * @param string $memo
   * @return Operation
   */
  public function setMemo($memo)
  {
    $this->memo = $memo;
  
    return $this;
  }

  /**
   * Get memo
   *
   * @return string 
   */
  public function getMemo()
  {
    return $this->memo;
  }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->operation_records = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set object
     *
     * @param \Woojin\AgencyBundle\Entity\Object $object
     * @return Operation
     */
    public function setObject(\Woojin\AgencyBundle\Entity\Object $object = null)
    {
        $this->object = $object;
    
        return $this;
    }

    /**
     * Get object
     *
     * @return \Woojin\AgencyBundle\Entity\Object 
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * Set operation_kind
     *
     * @param \Woojin\AgencyBundle\Entity\OperationKind $operationKind
     * @return Operation
     */
    public function setOperationKind(\Woojin\AgencyBundle\Entity\OperationKind $operationKind = null)
    {
        $this->operation_kind = $operationKind;
    
        return $this;
    }

    /**
     * Get operation_kind
     *
     * @return \Woojin\AgencyBundle\Entity\OperationKind 
     */
    public function getOperationKind()
    {
        return $this->operation_kind;
    }

    /**
     * Set operation_status
     *
     * @param \Woojin\AgencyBundle\Entity\OperationStatus $operationStatus
     * @return Operation
     */
    public function setOperationStatus(\Woojin\AgencyBundle\Entity\OperationStatus $operationStatus = null)
    {
        $this->operation_status = $operationStatus;
    
        return $this;
    }

    /**
     * Get operation_status
     *
     * @return \Woojin\AgencyBundle\Entity\OperationStatus 
     */
    public function getOperationStatus()
    {
        return $this->operation_status;
    }

    /**
     * Add operation_records
     *
     * @param \Woojin\AgencyBundle\Entity\OperationRecord $operationRecords
     * @return Operation
     */
    public function addOperationRecord(\Woojin\AgencyBundle\Entity\OperationRecord $operationRecords)
    {
        $this->operation_records[] = $operationRecords;
    
        return $this;
    }

    /**
     * Remove operation_records
     *
     * @param \Woojin\AgencyBundle\Entity\OperationRecord $operationRecords
     */
    public function removeOperationRecord(\Woojin\AgencyBundle\Entity\OperationRecord $operationRecords)
    {
        $this->operation_records->removeElement($operationRecords);
    }

    /**
     * Get operation_records
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOperationRecords()
    {
        return $this->operation_records;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }
}
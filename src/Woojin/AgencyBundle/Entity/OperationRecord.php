<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OperationRecord
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class OperationRecord
{
  /**
   * @ORM\ManyToOne(targetEntity="Operation", inversedBy="operation_records")
   * @var Operation
   */
  protected $operation;

  /**
   * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="operation_records")
   * @var User
   */
  protected $user;

  /**
   * @ORM\PrePersist
   */
  public function setDoAtValue()
  {
    $this->setDoAt(new \Datetime());
  }

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
   * @ORM\Column(name="do_at", type="datetime")
   */
  private $doAt;

  /**
   * @var string
   *
   * @ORM\Column(name="act", type="string", length=255)
   */
  private $act;


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
   * Set doAt
   *
   * @param \DateTime $doAt
   * @return OperationRecord
   */
  public function setDoAt($doAt)
  {
    $this->doAt = $doAt;
  
    return $this;
  }

  /**
   * Get doAt
   *
   * @return \DateTime 
   */
  public function getDoAt()
  {
    return $this->doAt;
  }

  /**
   * Set act
   *
   * @param string $act
   * @return OperationRecord
   */
  public function setAct($act)
  {
    $this->act = $act;
  
    return $this;
  }

  /**
   * Get act
   *
   * @return string 
   */
  public function getAct()
  {
    return $this->act;
  }

    /**
     * Set operation
     *
     * @param \Woojin\AgencyBundle\Entity\Operation $operation
     * @return OperationRecord
     */
    public function setOperation(\Woojin\AgencyBundle\Entity\Operation $operation = null)
    {
        $this->operation = $operation;
    
        return $this;
    }

    /**
     * Get operation
     *
     * @return \Woojin\AgencyBundle\Entity\Operation 
     */
    public function getOperation()
    {
        return $this->operation;
    }

    public function __toString()
    {
        return $this->getAct();
    }

    /**
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return OperationRecord
     */
    public function setUser(\Woojin\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Woojin\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}
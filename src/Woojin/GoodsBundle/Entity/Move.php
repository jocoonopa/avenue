<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Move
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class Move
{
    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="catchMoves")
     * 
     * @var Catcher
     */
    protected $catcher;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="throwMoves")
     * 
     * @var Thrower
     */
    protected $thrower;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\StoreBundle\Entity\Store", inversedBy="fromMoves")
     * @var From
     */
    protected $from;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\StoreBundle\Entity\Store", inversedBy="destinationMoves")
     * @var Destination
     */
    protected $destination;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="createMoves")
     * 
     * @var Creater
     */
    protected $creater;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="closeMoves")
     * 
     * @var Closer
     */
    protected $closer;

    /**
     * @ORM\ManyToOne(targetEntity="GoodsPassport", inversedBy="orgMoves")
     * @var OrgGoods
     */
    protected $orgGoods;

    /**
     * @ORM\ManyToOne(targetEntity="GoodsPassport", inversedBy="newMoves")
     * @var NewGoods
     */
    protected $newGoods;

    /**
     * @ORM\ManyToOne(targetEntity="MoveStatus", inversedBy="moves")
     * 
     * @var Status
     */
    protected $status;

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
     * @ORM\Column(name="create_at", type="datetime")
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime")
     */
    private $updateAt;

    /**
     * @var string
     *
     * @ORM\Column(name="memo", type="text", nullable=true)
     */
    private $memo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_read", type="boolean")
     */
    private $hasRead;

    public function __construct()
    {
        $this->hasRead = false;
    }

    /**
     * @ORM\PrePersist
     */
    public function autoSetCreateAt()
    {
        $this->setCreateAt(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function autoSetUpdateAt()
    {
        $this->setUpdateAt(new \Datetime());
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
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return Move
     */
    public function setCreateAt($createAt)
    {
        $this->createAt = $createAt;
    
        return $this;
    }

    /**
     * Get createAt
     *
     * @return \DateTime 
     */
    public function getCreateAt()
    {
        return $this->createAt;
    }

    /**
     * Set updateAt
     *
     * @param \DateTime $updateAt
     * @return Move
     */
    public function setUpdateAt($updateAt)
    {
        $this->updateAt = $updateAt;
    
        return $this;
    }

    /**
     * Get updateAt
     *
     * @return \DateTime 
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return Move
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
     * Set catcher
     *
     * @param \Woojin\UserBundle\Entity\User $catcher
     * @return Move
     */
    public function setCatcher(\Woojin\UserBundle\Entity\User $catcher = null)
    {
        $this->catcher = $catcher;
    
        return $this;
    }

    /**
     * Get catcher
     *
     * @return \Woojin\UserBundle\Entity\User 
     */
    public function getCatcher()
    {
        return $this->catcher;
    }

    /**
     * Set thrower
     *
     * @param \Woojin\UserBundle\Entity\User $thrower
     * @return Move
     */
    public function setThrower(\Woojin\UserBundle\Entity\User $thrower = null)
    {
        $this->thrower = $thrower;
    
        return $this;
    }

    /**
     * Get thrower
     *
     * @return \Woojin\UserBundle\Entity\User 
     */
    public function getThrower()
    {
        return $this->thrower;
    }

    /**
     * Set from
     *
     * @param \Woojin\StoreBundle\Entity\Store $from
     * @return Move
     */
    public function setFrom(\Woojin\StoreBundle\Entity\Store $from = null)
    {
        $this->from = $from;
    
        return $this;
    }

    /**
     * Get from
     *
     * @return \Woojin\StoreBundle\Entity\Store 
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set destination
     *
     * @param \Woojin\StoreBundle\Entity\Store $destination
     * @return Move
     */
    public function setDestination(\Woojin\StoreBundle\Entity\Store $destination = null)
    {
        $this->destination = $destination;
    
        return $this;
    }

    /**
     * Get destination
     *
     * @return \Woojin\StoreBundle\Entity\Store 
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Set creater
     *
     * @param \Woojin\UserBundle\Entity\User $creater
     * @return Move
     */
    public function setCreater(\Woojin\UserBundle\Entity\User $creater = null)
    {
        $this->creater = $creater;
    
        return $this;
    }

    /**
     * Get creater
     *
     * @return \Woojin\UserBundle\Entity\User 
     */
    public function getCreater()
    {
        return $this->creater;
    }

    /**
     * Set closer
     *
     * @param \Woojin\UserBundle\Entity\User $closer
     * @return Move
     */
    public function setCloser(\Woojin\UserBundle\Entity\User $closer = null)
    {
        $this->closer = $closer;
    
        return $this;
    }

    /**
     * Get closer
     *
     * @return \Woojin\UserBundle\Entity\User 
     */
    public function getCloser()
    {
        return $this->closer;
    }

    /**
     * Set orgGoods
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $orgGoods
     * @return Move
     */
    public function setOrgGoods(\Woojin\GoodsBundle\Entity\GoodsPassport $orgGoods = null)
    {
        $this->orgGoods = $orgGoods;
    
        return $this;
    }

    /**
     * Get orgGoods
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport 
     */
    public function getOrgGoods()
    {
        return $this->orgGoods;
    }

    /**
     * Set newGoods
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $newGoods
     * @return Move
     */
    public function setNewGoods(\Woojin\GoodsBundle\Entity\GoodsPassport $newGoods = null)
    {
        $this->newGoods = $newGoods;
    
        return $this;
    }

    /**
     * Get newGoods
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport 
     */
    public function getNewGoods()
    {
        return $this->newGoods;
    }

    /**
     * Set moveStatus
     *
     * @param \Woojin\GoodsBundle\Entity\MoveStatus $moveStatus
     * @return Move
     */
    public function setMoveStatus(\Woojin\GoodsBundle\Entity\MoveStatus $moveStatus = null)
    {
        $this->moveStatus = $moveStatus;
    
        return $this;
    }

    /**
     * Get moveStatus
     *
     * @return \Woojin\GoodsBundle\Entity\MoveStatus 
     */
    public function getMoveStatus()
    {
        return $this->moveStatus;
    }

    /**
     * Set hasRead
     *
     * @param boolean $hasRead
     * @return Move
     */
    public function setHasRead($hasRead)
    {
        $this->hasRead = $hasRead;
    
        return $this;
    }

    /**
     * Get hasRead
     *
     * @return boolean 
     */
    public function getHasRead()
    {
        return $this->hasRead;
    }

    /**
     * Set status
     *
     * @param \Woojin\GoodsBundle\Entity\MoveStatus $status
     * @return Move
     */
    public function setStatus(\Woojin\GoodsBundle\Entity\MoveStatus $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Woojin\GoodsBundle\Entity\MoveStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Facade of MoveStatus isModifyble
     * 
     * @return boolean
     */
    public function isModifyble()
    {
        return $this->status->isModifyble();
    }
}
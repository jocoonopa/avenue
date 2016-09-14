<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Woojin\Utility\Avenue\Avenue;

/**
 * MoveStatus
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MoveStatus
{
    /**
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\Move", mappedBy="status")
     * @var Moves[]
     */
    protected $moves;

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
     * @ORM\Column(name="name", type="string", length=10)
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
     * @return MoveStatus
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
        $this->moves = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add moves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $moves
     * @return MoveStatus
     */
    public function addMove(\Woojin\GoodsBundle\Entity\Move $moves)
    {
        $this->moves[] = $moves;
    
        return $this;
    }

    /**
     * Remove moves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $moves
     */
    public function removeMove(\Woojin\GoodsBundle\Entity\Move $moves)
    {
        $this->moves->removeElement($moves);
    }

    /**
     * Get moves
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMoves()
    {
        return $this->moves;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Determoned move isModifyble
     * 
     * @return boolean
     */
    public function isModifyble()
    {
        return !in_array($this->id, array(Avenue::MV_COMPLETE, Avenue::MV_CANCEL, Avenue::MV_REJECT));
    }
}

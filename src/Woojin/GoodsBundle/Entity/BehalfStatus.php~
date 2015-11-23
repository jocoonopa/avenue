<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * BehalfStatus
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class BehalfStatus
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Behalf", mappedBy="status")
     * @var Behalfs[]
     */
    protected $behalfs;

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
     * @ORM\Column(name="name", type="string", length=50)
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
     * @return BehalfStatus
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
        $this->behalfs = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add behalfs
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $behalfs
     * @return BehalfStatus
     */
    public function addBehalf(\Woojin\GoodsBundle\Entity\Behalf $behalfs)
    {
        $this->behalfs[] = $behalfs;
    
        return $this;
    }

    /**
     * Remove behalfs
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $behalfs
     */
    public function removeBehalf(\Woojin\GoodsBundle\Entity\Behalf $behalfs)
    {
        $this->behalfs->removeElement($behalfs);
    }

    /**
     * Get behalfs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBehalfs()
    {
        return $this->behalfs;
    }
}
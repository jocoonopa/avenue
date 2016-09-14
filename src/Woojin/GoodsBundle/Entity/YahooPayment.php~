<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * YahooPayment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class YahooPayment
{
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
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isDefaultOn", type="boolean")
     */
    private $isDefaultOn;


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
     * @return YahooPayment
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
     * Set isDefaultOn
     *
     * @param boolean $isDefaultOn
     * @return YahooPayment
     */
    public function setIsDefaultOn($isDefaultOn)
    {
        $this->isDefaultOn = $isDefaultOn;
    
        return $this;
    }

    /**
     * Get isDefaultOn
     *
     * @return boolean 
     */
    public function getIsDefaultOn()
    {
        return $this->isDefaultOn;
    }
}

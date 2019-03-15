<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Contractor
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Contractor
{
    /**
     * @ORM\OneToMany(targetEntity="AgencyObject", mappedBy="contractor")
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
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="memo", type="text")
     */
    private $memo;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="string", length=255)
     */
    private $contact;


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
     * @return Contractor
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
     * Set phone
     *
     * @param string $phone
     * @return Contractor
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return Contractor
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
     * Set contact
     *
     * @param string $contact
     * @return Contractor
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    
        return $this;
    }

    /**
     * Get contact
     *
     * @return string 
     */
    public function getContact()
    {
        return $this->contact;
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
     * @return Contractor
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
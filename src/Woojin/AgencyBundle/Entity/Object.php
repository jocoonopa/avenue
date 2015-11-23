<?php

namespace Woojin\AgencyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Object
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Object
{
  /**
   * @ORM\ManyToOne(targetEntity="\Woojin\OrderBundle\Entity\Custom", inversedBy="objects")
   * @var Custom
   */
  protected $custom;

  /**
   * @ORM\ManyToOne(targetEntity="Contractor", inversedBy="objects")
   * @var Contractor
   */
  protected $contractor;

  /**
   * @ORM\ManyToOne(targetEntity="ObjectStatus", inversedBy="objects")
   * @var ObjectStatus
   */
  protected $object_status;

  /**
   * @ORM\ManyToOne(targetEntity="ObjectLocation", inversedBy="objects")
   * @var ObjectLocation
   */
  protected $object_location;

  /**
   * @ORM\ManyToOne(targetEntity="AgencyItem", inversedBy="objects")
   * @var AgencyItem
   */
  protected $agency_item;

  /**
   * @ORM\OneToMany(targetEntity="ObjectImage", mappedBy="object")
   * @var ObjectImage[]
   */
  protected $object_images;

  /**
   * @ORM\OneToMany(targetEntity="Operation", mappedBy="object")
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
   * @var string
   *
   * @ORM\Column(name="sn", type="string", length=255)
   */
  private $sn;

  /**
   * @var string
   *
   * @ORM\Column(name="brand", type="string", length=255)
   */
  private $brand;

  /**
   * @var integer
   *
   * @ORM\Column(name="custom_price", type="integer", length=255)
   */
  private $customPrice;

  /**
   * @var integer
   *
   * @ORM\Column(name="contractor_price", type="integer", length=255)
   */
  private $contractorPrice;

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
   * Set name
   *
   * @param string $name
   * @return Object
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
   * Set sn
   *
   * @param string $sn
   * @return Object
   */
  public function setSn($sn)
  {
    $this->sn = $sn;
  
    return $this;
  }

  /**
   * Get sn
   *
   * @return string 
   */
  public function getSn()
  {
    return $this->sn;
  }

  /**
   * Set brand
   *
   * @param string $brand
   * @return Object
   */
  public function setBrand($brand)
  {
    $this->brand = $brand;
  
    return $this;
  }

  /**
   * Get brand
   *
   * @return string 
   */
  public function getBrand()
  {
    return $this->brand;
  }

  /**
   * Set memo
   *
   * @param string $memo
   * @return Object
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
    $this->object_images = new \Doctrine\Common\Collections\ArrayCollection();
    $this->operations = new \Doctrine\Common\Collections\ArrayCollection();
  }
  
  /**
   * Set object_status
   *
   * @param \Woojin\AgencyBundle\Entity\ObjectStatus $objectStatus
   * @return Object
   */
  public function setObjectStatus(\Woojin\AgencyBundle\Entity\ObjectStatus $objectStatus = null)
  {
    $this->object_status = $objectStatus;
  
    return $this;
  }

  /**
   * Get object_status
   *
   * @return \Woojin\AgencyBundle\Entity\ObjectStatus 
   */
  public function getObjectStatus()
  {
    return $this->object_status;
  }

  /**
   * Set object_location
   *
   * @param \Woojin\AgencyBundle\Entity\ObjectLocation $objectLocation
   * @return Object
   */
  public function setObjectLocation(\Woojin\AgencyBundle\Entity\ObjectLocation $objectLocation = null)
  {
    $this->object_location = $objectLocation;
  
    return $this;
  }

  /**
   * Get object_location
   *
   * @return \Woojin\AgencyBundle\Entity\ObjectLocation 
   */
  public function getObjectLocation()
  {
    return $this->object_location;
  }

  /**
   * Set agency_item
   *
   * @param \Woojin\AgencyBundle\Entity\AgencyItem $agencyItem
   * @return Object
   */
  public function setAgencyItem(\Woojin\AgencyBundle\Entity\AgencyItem $agencyItem = null)
  {
    $this->agency_item = $agencyItem;
  
    return $this;
  }

  /**
   * Get agency_item
   *
   * @return \Woojin\AgencyBundle\Entity\AgencyItem 
   */
  public function getAgencyItem()
  {
    return $this->agency_item;
  }

  /**
   * Add object_images
   *
   * @param \Woojin\AgencyBundle\Entity\ObjectImage $objectImages
   * @return Object
   */
  public function addObjectImage(\Woojin\AgencyBundle\Entity\ObjectImage $objectImages)
  {
    $this->object_images[] = $objectImages;
  
    return $this;
  }

  /**
   * Remove object_images
   *
   * @param \Woojin\AgencyBundle\Entity\ObjectImage $objectImages
   */
  public function removeObjectImage(\Woojin\AgencyBundle\Entity\ObjectImage $objectImages)
  {
    $this->object_images->removeElement($objectImages);
  }

  /**
   * Get object_images
   *
   * @return \Doctrine\Common\Collections\Collection 
   */
  public function getObjectImages()
  {
    return $this->object_images;
  }

  /**
   * Add operations
   *
   * @param \Woojin\AgencyBundle\Entity\Operation $operations
   * @return Object
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

  /**
   * Set contractor
   *
   * @param \Woojin\AgencyBundle\Entity\Contractor $contractor
   * @return Object
   */
  public function setContractor(\Woojin\AgencyBundle\Entity\Contractor $contractor = null)
  {
    $this->contractor = $contractor;

    return $this;
  }

  /**
   * Get contractor
   *
   * @return \Woojin\AgencyBundle\Entity\Contractor 
   */
  public function getContractor()
  {
    return $this->contractor;
  }

  /**
   * Set customPrice
   *
   * @param integer $customPrice
   * @return Object
   */
  public function setCustomPrice($customPrice)
  {
    $this->customPrice = $customPrice;

    return $this;
  }

  /**
   * Get customPrice
   *
   * @return integer 
   */
  public function getCustomPrice()
  {
    return $this->customPrice;
  }

  /**
   * Set contractorPrice
   *
   * @param integer $contractorPrice
   * @return Object
   */
  public function setContractorPrice($contractorPrice)
  {
    $this->contractorPrice = $contractorPrice;

    return $this;
  }

  /**
   * Get contractorPrice
   *
   * @return integer 
   */
  public function getContractorPrice()
  {
    return $this->contractorPrice;
  }

  public function __toString()
  {
    return $this->getName();
  }

    /**
     * Set custom
     *
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     * @return Object
     */
    public function setCustom(\Woojin\OrderBundle\Entity\Custom $custom = null)
    {
        $this->custom = $custom;
    
        return $this;
    }

    /**
     * Get custom
     *
     * @return \Woojin\OrderBundle\Entity\Custom 
     */
    public function getCustom()
    {
        return $this->custom;
    }
}
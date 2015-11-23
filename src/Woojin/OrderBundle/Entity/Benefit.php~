<?php

namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Benefit
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\OrderBundle\Entity\BenefitRepository")
 */
class Benefit
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="BenefitFrag", mappedBy="from")
     * @var Frags[]
     */
    protected $frags;

    /**
     * @ORM\ManyToOne(targetEntity="Custom", inversedBy="benefits")
     * @var Custom
     */
    protected $custom;

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
     * @ORM\Column(name="expire_at", type="datetime")
     */
    private $expireAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime")
     */
    private $createAt;

    /**
     * @var string
     *
     * @ORM\Column(name="des", type="string", length=30)
     */
    private $des;

    /**
     * @var integer
     *
     * @ORM\Column(name="total", type="integer")
     */
    private $total;

    /**
     * @var integer
     *
     * @ORM\Column(name="remain", type="integer")
     */
    private $remain;


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
     * Set expireAt
     *
     * @param \DateTime $expireAt
     * @return Benefit
     */
    public function setExpireAt($expireAt)
    {
        $this->expireAt = $expireAt;
    
        return $this;
    }

    /**
     * Get expireAt
     *
     * @return \DateTime 
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return Benefit
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
     * Set des
     *
     * @param string $des
     * @return Benefit
     */
    public function setDes($des)
    {
        $this->des = $des;
    
        return $this;
    }

    /**
     * Get des
     *
     * @return string 
     */
    public function getDes()
    {
        return $this->des;
    }

    /**
     * Set total
     *
     * @param integer $total
     * @return Benefit
     */
    public function setTotal($total)
    {
        $this->total = $total;
    
        return $this;
    }

    /**
     * Get total
     *
     * @return integer 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set remain
     *
     * @param integer $remain
     * @return Benefit
     */
    public function setRemain($remain)
    {
        $this->remain = $remain;
    
        return $this;
    }

    /**
     * Get remain
     *
     * @return integer 
     */
    public function getRemain()
    {
        return $this->remain;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->frags = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add frags
     *
     * @param \Woojin\OrderBundle\Entity\BenefitFrag $frags
     * @return Benefit
     */
    public function addFrag(\Woojin\OrderBundle\Entity\BenefitFrag $frags)
    {
        $this->frags[] = $frags;
    
        return $this;
    }

    /**
     * Remove frags
     *
     * @param \Woojin\OrderBundle\Entity\BenefitFrag $frags
     */
    public function removeFrag(\Woojin\OrderBundle\Entity\BenefitFrag $frags)
    {
        $this->frags->removeElement($frags);
    }

    /**
     * Get frags
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFrags()
    {
        return $this->frags;
    }

    /**
     * Set custom
     *
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     * @return Benefit
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
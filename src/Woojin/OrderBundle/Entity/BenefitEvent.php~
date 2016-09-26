<?php

namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * BenefitEvent
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class BenefitEvent
{
    /**
     * @var Rules[]
     *
     * @Exclude()
     * @ORM\OneToMany(targetEntity="BenefitRule", mappedBy="event")
     */
    protected $rules;

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
     * @ORM\Column(name="start_at", type="datetime")
     */
    private $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_at", type="datetime")
     */
    private $endAt;

    /**
     * @ORM\Column(type="text", length=255)
     */
    private $des;

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
     * @return BenefitEvent
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
     * Set startAt
     *
     * @param \DateTime $startAt
     * @return BenefitEvent
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
    
        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime 
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set endAt
     *
     * @param \DateTime $endAt
     * @return BenefitEvent
     */
    public function setEndAt($endAt)
    {
        $this->endAt = $endAt;
    
        return $this;
    }

    /**
     * Get endAt
     *
     * @return \DateTime 
     */
    public function getEndAt()
    {
        return $this->endAt;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rules = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set des
     *
     * @param string $des
     * @return BenefitEvent
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
     * Add rules
     *
     * @param \Woojin\OrderBundle\Entity\BenefitRule $rules
     * @return BenefitEvent
     */
    public function addRule(\Woojin\OrderBundle\Entity\BenefitRule $rules)
    {
        $this->rules[] = $rules;
    
        return $this;
    }

    /**
     * Remove rules
     *
     * @param \Woojin\OrderBundle\Entity\BenefitRule $rules
     */
    public function removeRule(\Woojin\OrderBundle\Entity\BenefitRule $rules)
    {
        $this->rules->removeElement($rules);
    }

    /**
     * Get rules
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRules()
    {
        return $this->rules;
    }
}

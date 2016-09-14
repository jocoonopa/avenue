<?php

namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Holiday
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\StoreBundle\Entity\HolidayRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Holiday
{
    /**
    * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="holidays")
    * @var User
    */
    protected $user;

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
     * @var array
     *
     * @ORM\Column(name="schedule", type="json_array")
     */
    private $schedule;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime")
     */
    private $updateAt;

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function autoSetUpdateAt()
    {
        $this->setUpdateAt(new \Datetime());
    }

    public function getClassName()
    {
        return __CLASS__;
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
     * @return Holiday
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
     * Set schedule
     *
     * @param array $schedule
     * @return Holiday
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
    
        return $this;
    }

    /**
     * Get schedule
     *
     * @return array 
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * Set updateAt
     *
     * @param \DateTime $updateAt
     * @return Holiday
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
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return Holiday
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

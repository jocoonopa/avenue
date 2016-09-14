<?php

namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="meta_record")
 */
class MetaRecord
{
    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="meta_records")
     * @var User
     */
    protected $user;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $act;

    /**
     * @ORM\Column(type="datetime", length=50)
     */
    private $datetime;

    /**
     * Get meta_record_id
     *
     * @return integer 
     */
    public function getMetaRecordId()
    {
        return $this->meta_record_id;
    }

    /**
     * Set meta_record_act
     *
     * @param string $metaRecordAct
     * @return MetaRecord
     */
    public function setMetaRecordAct($metaRecordAct)
    {
        $this->meta_record_act = $metaRecordAct;
    
        return $this;
    }

    /**
     * Get meta_record_act
     *
     * @return string 
     */
    public function getMetaRecordAct()
    {
        return $this->meta_record_act;
    }

    /**
     * Set meta_record_datetime
     *
     * @param \DateTime $metaRecordDatetime
     * @return MetaRecord
     */
    public function setMetaRecordDatetime($metaRecordDatetime)
    {
        $this->meta_record_datetime = new \DateTime($metaRecordDatetime);
    
        return $this;
    }

    /**
     * Get meta_record_datetime
     *
     * @return \DateTime 
     */
    public function getMetaRecordDatetime()
    {
        return $this->meta_record_datetime;
    }

    /**
     * Set users_id
     *
     * @param integer $usersId
     * @return MetaRecord
     */
    public function setUsersId($usersId)
    {
        $this->users_id = $usersId;
    
        return $this;
    }

    /**
     * Get users_id
     *
     * @return integer 
     */
    public function getUsersId()
    {
        return $this->users_id;
    }

    /**
     * Set users
     *
     * @param \Woojin\UserBundle\Entity\User $users
     * @return MetaRecord
     */
    public function setUsers(\Woojin\UserBundle\Entity\User $users = null)
    {
        $this->users = $users;
    
        return $this;
    }

    /**
     * Get users
     *
     * @return \Woojin\UserBundle\Entity\User 
     */
    public function getUsers()
    {
        return $this->users;
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
     * Set act
     *
     * @param string $act
     * @return MetaRecord
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
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return MetaRecord
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;
    
        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return MetaRecord
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

<?php 
namespace Woojin\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users_log")
 */
class UsersLog
{
  /**
   * @ORM\ManyToOne(targetEntity="User", inversedBy="users_logs")
   * @var User
   */
  protected $user;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\Column(type="datetime", length=50)
   */
  protected $createtime;

  /**
   * @ORM\Column(type="string", length=30)
   */
  protected $ip;

  /**
   * @ORM\Column(type="string", length=150, nullable=true)
   */
  protected $error;

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
     * Set createtime
     *
     * @param \DateTime $createtime
     * @return UsersLog
     */
    public function setCreatetime($createtime)
    {
        $this->createtime = $createtime;
    
        return $this;
    }

    /**
     * Get createtime
     *
     * @return \DateTime 
     */
    public function getCreatetime()
    {
        return $this->createtime;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return UsersLog
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    
        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set error
     *
     * @param string $error
     * @return UsersLog
     */
    public function setError($error)
    {
        $this->error = $error;
    
        return $this;
    }

    /**
     * Get error
     *
     * @return string 
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return UsersLog
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

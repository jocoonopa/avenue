<?php

namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Woojin\OrderBundle\Entity\Custom;

/**
 * CustomOpe
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\OrderBundle\Entity\CustomOpeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CustomOpe
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
     * @ORM\ManyToOne(targetEntity="\Woojin\OrderBundle\Entity\Custom", inversedBy="customOpes")
     * @var Custom
     */
    protected $custom;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=255)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="at", type="datetime")
     */
    private $at;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=20)
     */
    private $ip;

    public function __construct(Custom $custom, $content)
    {
        $this->custom = $custom;
        $this->content = $content;
    }

    /**
     * @ORM\PrePersist
     */
    public function autoSetCreateAt()
    {
        $this->setAt(new \Datetime());
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
     * Set content
     *
     * @param string $content
     * @return CustomOpe
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set at
     *
     * @param \DateTime $at
     * @return CustomOpe
     */
    public function setAt($at)
    {
        $this->at = $at;
    
        return $this;
    }

    /**
     * Get at
     *
     * @return \DateTime 
     */
    public function getAt()
    {
        return $this->at;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return CustomOpe
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
     * Set custom
     *
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     * @return CustomOpe
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

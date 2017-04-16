<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BsoMoveLog
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class BsoMoveLog
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
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="bsoMoveLogs")
     */
    protected $creater;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\GoodsBundle\Entity\GoodsPassport", inversedBy="bsoMoveLogs")
     */
    protected $product;

    /**
     * @ORM\Column(type="datetime", length=50, nullable=true)
     */
    protected $createAt;

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
     * @ORM\PrePersist
     */
    public function autoSetCreateAt()
    {
        $this->setCreateAt(new \Datetime());
    }

    public function setCreateAt($date)
    {
        $this->createAt = $date;
    }

    /**
     * Set action
     *
     * @param string $action
     *
     * @return BsoMoveLog
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set creater
     *
     * @param \Woojin\UserBundle\Entity\User $creater
     *
     * @return BsoMoveLog
     */
    public function setCreater(\Woojin\UserBundle\Entity\User $creater = null)
    {
        $this->creater = $creater;

        return $this;
    }

    /**
     * Get creater
     *
     * @return \Woojin\UserBundle\Entity\User
     */
    public function getCreater()
    {
        return $this->creater;
    }

    /**
     * Set product
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $product
     *
     * @return BsoMoveLog
     */
    public function setProduct(\Woojin\GoodsBundle\Entity\GoodsPassport $product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport
     */
    public function getProduct()
    {
        return $this->product;
    }
}

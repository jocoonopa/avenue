<?php

namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AuctionPayment
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\StoreBundle\Entity\AuctionPaymentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AuctionPayment
{
    /**
     * @ORM\ManyToOne(targetEntity="Auction", inversedBy="payments")
     */
    private $auction;

    /**
     * @ORM\ManyToOne(targetEntity="Woojin\OrderBundle\Entity\PayType", inversedBy="auctionPayments")
     */
    private $payType;

    /**
     * @ORM\ManyToOne(targetEntity="Woojin\UserBundle\Entity\User", inversedBy="createAuctionPayments")
     */
    private $creater;

    /**
     * @ORM\ManyToOne(targetEntity="Woojin\UserBundle\Entity\User", inversedBy="cancelAuctionPayments")
     */
    private $canceller;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="org_amount", type="integer")
     */
    private $orgAmount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_cancel", type="boolean")
     */
    private $isCancel;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancel_at", type="datetime")
     */
    private $cancelAt;

    /**
     * @var string
     *
     * @ORM\Column(name="memo", type="text", nullable=true)
     */
    private $memo;

    /**
     * @var string
     *
     * @ORM\Column(name="paid_at", type="datetime")
     */
    private $paidAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime")
     */
    private $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime")
     */
    private $updateAt;

    /**
     * @ORM\PrePersist
     */
    public function autoSetCreateAt()
    {
        $this->setCreateAt(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function autoSetUpdateAt()
    {
        $this->setUpdateAt(new \Datetime());
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
     * Set amount
     *
     * @param integer $amount
     *
     * @return AuctionPayment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set orgAmount
     *
     * @param integer $orgAmount
     *
     * @return AuctionPayment
     */
    public function setOrgAmount($orgAmount)
    {
        $this->orgAmount = $orgAmount;

        return $this;
    }

    /**
     * Get orgAmount
     *
     * @return integer
     */
    public function getOrgAmount()
    {
        return $this->orgAmount;
    }

    /**
     * Set isCancel
     *
     * @param boolean $isCancel
     *
     * @return AuctionPayment
     */
    public function setIsCancel($isCancel)
    {
        $this->isCancel = $isCancel;

        return $this;
    }

    /**
     * Get isCancel
     *
     * @return boolean
     */
    public function getIsCancel()
    {
        return $this->isCancel;
    }

    /**
     * Set cancelAt
     *
     * @param \DateTime $cancelAt
     *
     * @return AuctionPayment
     */
    public function setCancelAt($cancelAt)
    {
        $this->cancelAt = $cancelAt;

        return $this;
    }

    /**
     * Get cancelAt
     *
     * @return \DateTime
     */
    public function getCancelAt()
    {
        return $this->cancelAt;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return AuctionPayment
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
     * Set updateAt
     *
     * @param \DateTime $updateAt
     *
     * @return AuctionPayment
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
     * Set auction
     *
     * @param \Woojin\StoreBundle\Entity\Auction $auction
     *
     * @return AuctionPayment
     */
    public function setAuction(\Woojin\StoreBundle\Entity\Auction $auction = null)
    {
        $this->auction = $auction;

        return $this;
    }

    /**
     * Get auction
     *
     * @return \Woojin\StoreBundle\Entity\Auction
     */
    public function getAuction()
    {
        return $this->auction;
    }

    /**
     * Set payType
     *
     * @param \Woojin\OrderBundle\Entity\PayType $payType
     *
     * @return AuctionPayment
     */
    public function setPayType(\Woojin\OrderBundle\Entity\PayType $payType = null)
    {
        $this->payType = $payType;

        return $this;
    }

    /**
     * Get payType
     *
     * @return \Woojin\OrderBundle\Entity\PayType
     */
    public function getPayType()
    {
        return $this->payType;
    }

    /**
     * Set creater
     *
     * @param \Woojin\UserBundle\Entity\User $creater
     *
     * @return AuctionPayment
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
     * Set canceller
     *
     * @param \Woojin\UserBundle\Entity\User $canceller
     *
     * @return AuctionPayment
     */
    public function setCanceller(\Woojin\UserBundle\Entity\User $canceller = null)
    {
        $this->canceller = $canceller;

        return $this;
    }

    /**
     * Get canceller
     *
     * @return \Woojin\UserBundle\Entity\User
     */
    public function getCanceller()
    {
        return $this->canceller;
    }

    /**
     * Set memo
     *
     * @param string $memo
     *
     * @return AuctionPayment
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
     * Set paidAt
     *
     * @param \DateTime $paidAt
     *
     * @return AuctionPayment
     */
    public function setPaidAt($paidAt)
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    /**
     * Get paidAt
     *
     * @return \DateTime
     */
    public function getPaidAt()
    {
        return $this->paidAt;
    }
}

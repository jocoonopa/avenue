<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Woojin\GoodsBundle\Entity\BehalfStatus;
use Woojin\UserBundle\Entity\User;
use Woojin\OrderBundle\Entity\Order;
use Woojin\OrderBundle\Entity\Custom;

/**
 * Behalf
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\GoodsBundle\Entity\BehalfRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Behalf
{
    /**
    * @ORM\ManyToOne(targetEntity="Woojin\OrderBundle\Entity\Custom", inversedBy="behalfs")
    * @var Want
    */
    private $custom;

    /**
    * @ORM\ManyToOne(targetEntity="Woojin\UserBundle\Entity\User", inversedBy="behalfs")
    * @var Want
    */
    private $user;

    /**
     * @ORM\OneToOne(targetEntity="GoodsPassport", mappedBy="gotBehalf")
     **/
    private $got;

    /**
    * @ORM\ManyToOne(targetEntity="GoodsPassport", inversedBy="wantBehalfs")
    * @var Want
    */
    protected $want;

    /**
    * @ORM\ManyToOne(targetEntity="BehalfStatus", inversedBy="behalfs")
    * @var Status
    */
    protected $status;

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
     * @ORM\Column(name="required", type="integer")
     */
    private $required;

    /**
     * @var string
     *
     * @ORM\Column(name="memo", type="text", nullable=true)
     */
    private $memo;

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
     * @var \DateTime
     *
     * @ORM\Column(name="pay_at", type="datetime", nullable=true)
     */
    private $payAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="confirm_first_at", type="datetime", nullable=true)
     */
    private $confirmFirstAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="confirm_second_at", type="datetime", nullable=true)
     */
    private $confirmSecondAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="in_at", type="datetime", nullable=true)
     */
    private $inAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="send_at", type="datetime", nullable=true)
     */
    private $sendAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cancel_at", type="datetime", nullable=true)
     */
    private $cancelAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="acancel_at", type="datetime", nullable=true)
     */
    private $acancelAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="charge_back_at", type="datetime", nullable=true)
     */
    private $chargeBackAt;

    /**
     * @ORM\Column(type="string", length=20)
     */
    protected $phone;

    /**
     * 宅配單號
     * 
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $deliverySn;

    /**
     * 銀行帳戶末五碼
     * 
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $bankAccount;

    /**
     * 銀行代碼
     * 
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $bankCode;

    public function __construct(Custom $custom, GoodsPassport $want, BehalfStatus $status, $phone)
    {
        $this->status = $status;
        $this->custom = $custom;
        $this->want = $want;
        $this->phone = $phone;
        $this->required = 0;
        $this->deposit = 0;
        $this->paid = 0;
    }

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
     * Set paid
     *
     * @param integer $paid
     * @return Behalf
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
    
        return $this;
    }

    /**
     * Get paid
     *
     * @return integer 
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set required
     *
     * @param integer $required
     * @return Behalf
     */
    public function setRequired($required)
    {
        $this->required = $required;
    
        return $this;
    }

    /**
     * Get required
     *
     * @return integer 
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set deposit
     *
     * @param integer $deposit
     * @return Behalf
     */
    public function setDeposit($deposit)
    {
        $this->deposit = $deposit;
    
        return $this;
    }

    /**
     * Get deposit
     *
     * @return integer 
     */
    public function getDeposit()
    {
        return $this->deposit;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return Behalf
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
     * Set got
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $got
     * @return Behalf
     */
    public function setGot(\Woojin\GoodsBundle\Entity\GoodsPassport $got = null)
    {
        $this->got = $got;
    
        return $this;
    }

    /**
     * Get got
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport 
     */
    public function getGot()
    {
        return $this->got;
    }

    /**
     * Set want
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $want
     * @return Behalf
     */
    public function setWant(\Woojin\GoodsBundle\Entity\GoodsPassport $want = null)
    {
        $this->want = $want;
    
        return $this;
    }

    /**
     * Get want
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport 
     */
    public function getWant()
    {
        return $this->want;
    }

    /**
     * Set status
     *
     * @param \Woojin\GoodsBundle\Entity\BehalfStatus $status
     * @return Behalf
     */
    public function setStatus(\Woojin\GoodsBundle\Entity\BehalfStatus $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Woojin\GoodsBundle\Entity\BehalfStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return Behalf
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
     * @return Behalf
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
     * Set custom
     *
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     * @return Behalf
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

    /**
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return Behalf
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

    /**
     * Set payAt
     *
     * @param \DateTime $payAt
     * @return Behalf
     */
    public function setPayAt($payAt)
    {
        $this->payAt = $payAt;
    
        return $this;
    }

    /**
     * Get payAt
     *
     * @return \DateTime 
     */
    public function getPayAt()
    {
        return $this->payAt;
    }

    /**
     * Set confirmFirstAt
     *
     * @param \DateTime $confirmFirstAt
     * @return Behalf
     */
    public function setConfirmFirstAt($confirmFirstAt)
    {
        $this->confirmFirstAt = $confirmFirstAt;
    
        return $this;
    }

    /**
     * Get confirmFirstAt
     *
     * @return \DateTime 
     */
    public function getConfirmFirstAt()
    {
        return $this->confirmFirstAt;
    }

    /**
     * Set confirmSecondAt
     *
     * @param \DateTime $confirmSecondAt
     * @return Behalf
     */
    public function setConfirmSecondAt($confirmSecondAt)
    {
        $this->confirmSecondAt = $confirmSecondAt;
    
        return $this;
    }

    /**
     * Get confirmSecondAt
     *
     * @return \DateTime 
     */
    public function getConfirmSecondAt()
    {
        return $this->confirmSecondAt;
    }

    /**
     * Set inAt
     *
     * @param \DateTime $inAt
     * @return Behalf
     */
    public function setInAt($inAt)
    {
        $this->inAt = $inAt;
    
        return $this;
    }

    /**
     * Get inAt
     *
     * @return \DateTime 
     */
    public function getInAt()
    {
        return $this->inAt;
    }

    /**
     * Set sendAt
     *
     * @param \DateTime $sendAt
     * @return Behalf
     */
    public function setSendAt($sendAt)
    {
        $this->sendAt = $sendAt;
    
        return $this;
    }

    /**
     * Get sendAt
     *
     * @return \DateTime 
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Behalf
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
     * Set deliverySn
     *
     * @param string $deliverySn
     * @return Behalf
     */
    public function setDeliverySn($deliverySn)
    {
        $this->deliverySn = $deliverySn;
    
        return $this;
    }

    /**
     * Get deliverySn
     *
     * @return string 
     */
    public function getDeliverySn()
    {
        return $this->deliverySn;
    }

    /**
     * Set bankAccount
     *
     * @param string $bankAccount
     * @return Behalf
     */
    public function setBankAccount($bankAccount)
    {
        $this->bankAccount = $bankAccount;
    
        return $this;
    }

    /**
     * Get bankAccount
     *
     * @return string 
     */
    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    /**
     * Set bankCode
     *
     * @param string $bankCode
     * @return Behalf
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = $bankCode;
    
        return $this;
    }

    /**
     * Get bankCode
     *
     * @return string 
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * Set acancelAt
     *
     * @param \DateTime $acancelAt
     * @return Behalf
     */
    public function setAcancelAt($acancelAt)
    {
        $this->acancelAt = $acancelAt;
    
        return $this;
    }

    /**
     * Get acancelAt
     *
     * @return \DateTime 
     */
    public function getAcancelAt()
    {
        return $this->acancelAt;
    }

    /**
     * Set chargeBackAt
     *
     * @param \DateTime $chargeBackAt
     * @return Behalf
     */
    public function setChargeBackAt($chargeBackAt)
    {
        $this->chargeBackAt = $chargeBackAt;
    
        return $this;
    }

    /**
     * Get chargeBackAt
     *
     * @return \DateTime 
     */
    public function getChargeBackAt()
    {
        return $this->chargeBackAt;
    }

    /**
     * Set cancelAt
     *
     * @param \DateTime $cancelAt
     * @return Behalf
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
}
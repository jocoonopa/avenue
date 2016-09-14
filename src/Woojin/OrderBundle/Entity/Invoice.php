<?php

namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\MaxDepth;

use Woojin\Utility\Avenue\Avenue;

/**
 * Invoice
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\OrderBundle\Entity\InvoiceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Invoice
{
    /**
     * @MaxDepth(2)
     * @ORM\OneToMany(targetEntity="\Woojin\OrderBundle\Entity\Orders", mappedBy="invoice")
     * @var Orders[]
     */
    protected $orders;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\StoreBundle\Entity\Store", inversedBy="invoices")
     * @var Store
     */
    protected $store;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\OrderBundle\Entity\Custom", inversedBy="invoices")
     * @var Custom
     */
    protected $custom;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="sn", type="string", length=255, nullable=true)
     */
    private $sn;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $isDiffAddress;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $county;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $district;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @var boolean
     *
     * @ORM\Column(name="has_print", type="boolean")
     */
    private $hasPrint;

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
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", length=30, nullable=true)
     */
    private $paymentType;

    /**
     * @var string
     *
     * @ORM\Column(name="trade_no", type="string", length=30, nullable=true)
     */
    private $tradeNo;

    /**
     * 是否圍毆付寶訂單
     * 
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $isAllPay;

    /**
     * 發票應付總金額
     * 
     * @var integer
     *
     * @ORM\Column(type="integer")
     * 
     */
    private $required;

    /**
     * 發票已付總金額
     * 
     * @var integer
     *
     * @ORM\Column(type="integer")
     * 
     */
    private $paid;

    /**
     * @var integer
     *
     * pos 銷貨無視此屬性，僅和官網有關
     *  
     * 0: 未被歐付寶接收
     * 1: 成功被接收 , 尚未出貨
     * 2: 結完, 出貨
     * 3: 送出退款請求通知
     * 4: 退款or取消通知已由香榭成員處理, 貨物尚未到店
     * 5: 退款且貨物已回到店內
     * 
     * @ORM\Column(type="integer")
     */
    protected $status;

    /**
     * @var integer
     *
     * 期數, 列表如下:
     * 一次性     2%
     * -3期   2.5%
     * -6期      4%
     * -12期     7%
     * -18期     9.5%
     * -24期     12%
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $creditInstallment;

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

    public function getInvoiceSurcharge()
    {
        $surcharge = 0;

        if ($this->creditInstallment && $this->paymentType === 'Credit_CreditCard') {
            switch ($this->creditInstallment)
            {
                case 0:
                    $surcharge = 2;
                    break;

                case 3:
                    $surcharge = 2.5;
                    break;

                case 6:
                    $surcharge = 4;
                    break;

                case 12:
                    $surcharge = 7;
                    break;

                case 18:
                    $surcharge = 9.5;
                    break;

                case 24:
                    $surcharge = 12;
                    break;

                default:
                    break;
            }
        }

        return $surcharge;
    }

    /**
     * 是否為該客戶的訂單
     * 
     * @param integer $id
     * @return boolean
     */
    public function isOwnInvoice($id)
    {
       return ($this->custom->getId() === (int) $id);
    }

    public function genSn()
    {
        return str_pad('', 12, '0', STR_PAD_LEFT) . $this->getId() . $this->getId() % 7 . 'a';
    }

    public function shiftSn()
    {
        $tail = substr($this->sn, -1);

        $this->sn = substr($this->sn, 0, -1) . (++ $tail);

        return $this;
    }

    public function getTotal()
    {
        $total = 0;
        
        foreach ($this->orders as $order) {
            // if ($order->getStatus()->getId() === Avenue::OS_CANCEL) {
            //     continue;
            // }

            $total += (int) $order->getOrgRequired();
        }

        return $total;
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
     * Set sn
     *
     * @param string $sn
     * @return Invoice
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
     * Set hasPrint
     *
     * @param boolean $hasPrint
     * @return Invoice
     */
    public function setHasPrint($hasPrint)
    {
        $this->hasPrint = $hasPrint;
    
        return $this;
    }

    /**
     * Get hasPrint
     *
     * @return boolean 
     */
    public function getHasPrint()
    {
        return $this->hasPrint;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return Invoice
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
     * @return Invoice
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
     * Constructor
     */
    public function __construct()
    {
        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isDiffAddress = false;
        $this->status = 0;// 0: 未被歐付寶接收, 1: 成功被接收, 尚未結完 2: 結完, 4:退款or
        $this->isAllPay = false;
        $this->county = '未填寫';
        $this->district = '未填寫';
        $this->required = 0;
        $this->paid = 0;
        $this->creditInstallment = 0;
    }

    public function getDiffNow()
    {
        return abs($this->updateAt->diff(new \DateTime())->format('%a'));
    }
    
    /**
     * Add orders
     *
     * @param \Woojin\OrderBundle\Entity\Orders $orders
     * @return Invoice
     */
    public function addOrder(\Woojin\OrderBundle\Entity\Orders $orders)
    {
        $this->orders[] = $orders;
    
        return $this;
    }

    /**
     * Remove orders
     *
     * @param \Woojin\OrderBundle\Entity\Orders $orders
     */
    public function removeOrder(\Woojin\OrderBundle\Entity\Orders $orders)
    {
        $this->orders->removeElement($orders);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Set store
     *
     * @param \Woojin\StoreBundle\Entity\Store $store
     * @return Invoice
     */
    public function setStore(\Woojin\StoreBundle\Entity\Store $store = null)
    {
        $this->store = $store;
    
        return $this;
    }

    /**
     * Get store
     *
     * @return \Woojin\StoreBundle\Entity\Store 
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Set custom
     *
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     * @return Invoice
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
     * Set isDiffAddress
     *
     * @param boolean $isDiffAddress
     * @return Invoice
     */
    public function setIsDiffAddress($isDiffAddress)
    {
        $this->isDiffAddress = $isDiffAddress;
    
        return $this;
    }

    /**
     * Get isDiffAddress
     *
     * @return boolean 
     */
    public function getIsDiffAddress()
    {
        return $this->isDiffAddress;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Invoice
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set payAt
     *
     * @param \DateTime $payAt
     * @return Invoice
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
     * Set paymentType
     *
     * @param string $paymentType
     * @return Invoice
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;
    
        return $this;
    }

    /**
     * Get paymentType
     *
     * @return string 
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * Set tradeNo
     *
     * @param string $tradeNo
     * @return Invoice
     */
    public function setTradeNo($tradeNo)
    {
        $this->tradeNo = $tradeNo;
    
        return $this;
    }

    /**
     * Get tradeNo
     *
     * @return string 
     */
    public function getTradeNo()
    {
        return $this->tradeNo;
    }

    /**
     * Set isAllPay
     *
     * @param boolean $isAllPay
     * @return Invoice
     */
    public function setIsAllPay($isAllPay)
    {
        $this->isAllPay = $isAllPay;
    
        return $this;
    }

    /**
     * Get isAllPay
     *
     * @return boolean 
     */
    public function getIsAllPay()
    {
        return $this->isAllPay;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Invoice
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set county
     *
     * @param string $county
     * @return Invoice
     */
    public function setCounty($county)
    {
        $this->county = $county;
    
        return $this;
    }

    /**
     * Get county
     *
     * @return string 
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set district
     *
     * @param string $district
     * @return Invoice
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    
        return $this;
    }

    /**
     * Get district
     *
     * @return string 
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * Set required
     *
     * @param integer $required
     * @return Invoice
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
     * Set paid
     *
     * @param integer $paid
     * @return Invoice
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
     * Set creditInstallment
     *
     * @param integer $creditInstallment
     * @return Invoice
     */
    public function setCreditInstallment($creditInstallment)
    {
        $this->creditInstallment = $creditInstallment;
    
        return $this;
    }

    /**
     * Get creditInstallment
     *
     * @return integer 
     */
    public function getCreditInstallment()
    {
        return $this->creditInstallment;
    }
}

<?php
namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

use Woojin\Utility\Avenue\Avenue;

/**
 * @ORM\Entity
 * @ORM\Table(name="orders")
 * @ORM\HasLifecycleCallbacks()
 */
class Orders
{
    /**
     * @ORM\OneToMany(targetEntity="BenefitFrag", mappedBy="order")
     * @var Fits[]
     */
    protected $fits;

    /**
     * @ORM\OneToMany(targetEntity="Ope", mappedBy="orders", orphanRemoval=true)
     * @var Ope[]
     */
    protected $opes;

    /**
     * @Exclude()
     * @ORM\ManyToOne(targetEntity="\Woojin\GoodsBundle\Entity\GoodsPassport", inversedBy="orders", cascade={"persist"})
     * @var GoodsPassport
     */
    protected $goods_passport;

    /**
     * @var Orders
     * @Exclude()
     * @ORM\ManyToOne(targetEntity="Orders", inversedBy="relates", cascade={"persist"})
     * @ORM\JoinColumn(name="related_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var Orders[]
     *
     * @Exclude()
     * @ORM\OneToMany(targetEntity="Orders", mappedBy="parent")
     */
    protected $relates;

    /**
     * @Exclude()
     * @ORM\ManyToOne(targetEntity="PayType", inversedBy="orders")
     * @var PayType
     */
    protected $pay_type;

    /**
     * @ORM\ManyToOne(targetEntity="OrdersStatus", inversedBy="orders")
     * @var Status
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="OrdersKind", inversedBy="orders")
     * @var Kind
     */
    protected $kind;

    /**
     * @ORM\ManyToOne(targetEntity="Custom", inversedBy="orders")
     * @var Custom
     */
    protected $custom;

    /**
     * @Exclude()
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="orders")
     * @var Invoice
     */
    protected $invoice;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    protected $memo;

    /**
     * @ORM\Column(type="integer", length=30, nullable=true)
     */
    protected $required;

    /**
     * @ORM\Column(type="integer", length=30, nullable=true)
     */
    protected $paid;

    /**
     * @ORM\Column(type="integer", length=30, nullable=true)
     */
    protected $org_required;

    /**
     * @ORM\Column(type="integer", length=30, nullable=true)
     */
    protected $org_paid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime")
     */
    protected $createAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_at", type="datetime")
     */
    protected $updateAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="manual_created_at", type="datetime", nullable=true)
     */
    protected $manualCreatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->opes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->relates = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getProduct()
    {
        return $this->goods_passport;
    }

    public function getLogicStatusCode()
    {
        return ($this->required == $this->paid) ? Avenue::OS_COMPLETE : Avenue::OS_HANDLING;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set memo
     *
     * @param string $memo
     * @return Orders
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
     * Set required
     *
     * @param integer $required
     * @return Orders
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
     * @return Orders
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
     * Set org_required
     *
     * @param integer $orgRequired
     * @return Orders
     */
    public function setOrgRequired($orgRequired)
    {
        $this->org_required = $orgRequired;

        return $this;
    }

    /**
     * Get org_required
     *
     * @return integer
     */
    public function getOrgRequired()
    {
        return $this->org_required;
    }

    /**
     * Set org_paid
     *
     * @param integer $orgPaid
     * @return Orders
     */
    public function setOrgPaid($orgPaid)
    {
        $this->org_paid = $orgPaid;

        return $this;
    }

    /**
     * Get org_paid
     *
     * @return integer
     */
    public function getOrgPaid()
    {
        return $this->org_paid;
    }

    /**
     * Add opes
     *
     * @param \Woojin\OrderBundle\Entity\Ope $opes
     * @return Orders
     */
    public function addOpe(\Woojin\OrderBundle\Entity\Ope $opes)
    {
        $this->opes[] = $opes;

        return $this;
    }

    /**
     * Remove opes
     *
     * @param \Woojin\OrderBundle\Entity\Ope $opes
     */
    public function removeOpe(\Woojin\OrderBundle\Entity\Ope $opes)
    {
        $this->opes->removeElement($opes);
    }

    /**
     * Get opes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOpes()
    {
        return $this->opes;
    }

    /**
     * Set goods_passport
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport
     * @return Orders
     */
    public function setGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport = null)
    {
        $this->goods_passport = $goodsPassport;

        return $this;
    }

    /**
     * Get goods_passport
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport
     */
    public function getGoodsPassport()
    {
        return $this->goods_passport;
    }

    /**
     * Set parent
     *
     * @param \Woojin\OrderBundle\Entity\Orders $parent
     * @return Orders
     */
    public function setParent(\Woojin\OrderBundle\Entity\Orders $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Woojin\OrderBundle\Entity\Orders
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add relates
     *
     * @param \Woojin\OrderBundle\Entity\Orders $relates
     * @return Orders
     */
    public function addRelate(\Woojin\OrderBundle\Entity\Orders $relates)
    {
        $this->relates[] = $relates;

        return $this;
    }

    /**
     * Remove relates
     *
     * @param \Woojin\OrderBundle\Entity\Orders $relates
     */
    public function removeRelate(\Woojin\OrderBundle\Entity\Orders $relates)
    {
        $this->relates->removeElement($relates);
    }

    /**
     * Get relates
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRelates()
    {
        return $this->relates;
    }

    /**
     * Set pay_type
     *
     * @param \Woojin\OrderBundle\Entity\PayType $payType
     * @return Orders
     */
    public function setPayType(\Woojin\OrderBundle\Entity\PayType $payType = null)
    {
        $this->pay_type = $payType;

        return $this;
    }

    /**
     * Get pay_type
     *
     * @return \Woojin\OrderBundle\Entity\PayType
     */
    public function getPayType()
    {
        return $this->pay_type;
    }

    /**
     * Set status
     *
     * @param \Woojin\OrderBundle\Entity\OrdersStatus $status
     * @return Orders
     */
    public function setStatus(\Woojin\OrderBundle\Entity\OrdersStatus $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Woojin\OrderBundle\Entity\OrdersStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set kind
     *
     * @param \Woojin\OrderBundle\Entity\OrdersKind $kind
     * @return Orders
     */
    public function setKind(\Woojin\OrderBundle\Entity\OrdersKind $kind = null)
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * Get kind
     *
     * @return \Woojin\OrderBundle\Entity\OrdersKind
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * Set custom
     *
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     * @return Orders
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
     * Set invoice
     *
     * @param \Woojin\OrderBundle\Entity\Invoice $invoice
     * @return Orders
     */
    public function setInvoice(\Woojin\OrderBundle\Entity\Invoice $invoice = null)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Get invoice
     *
     * @return \Woojin\OrderBundle\Entity\Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Add fits
     *
     * @param \Woojin\OrderBundle\Entity\BenefitFrag $fits
     * @return Orders
     */
    public function addFit(\Woojin\OrderBundle\Entity\BenefitFrag $fits)
    {
        $this->fits[] = $fits;

        return $this;
    }

    /**
     * Remove fits
     *
     * @param \Woojin\OrderBundle\Entity\BenefitFrag $fits
     */
    public function removeFit(\Woojin\OrderBundle\Entity\BenefitFrag $fits)
    {
        $this->fits->removeElement($fits);
    }

    /**
     * Get fits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFits()
    {
        return $this->fits;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return Orders
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
     * @return Orders
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
     * @return \DateTime
     */
    public function getManualCreatedAt()
    {
        return $this->manualCreatedAt;
    }

    /**
     * @param \DateTime $manualCreatedAt
     *
     * @return self
     */
    public function setManualCreatedAt($manualCreatedAt)
    {
        $this->manualCreatedAt = $manualCreatedAt;

        return $this;
    }
}

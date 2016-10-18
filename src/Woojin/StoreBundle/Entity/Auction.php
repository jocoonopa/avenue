<?php

namespace Woojin\StoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Exclude;
use Woojin\GoodsBundle\Entity\GoodsPassport;
use Woojin\Utility\Avenue\Avenue;
use Woojin\UserBundle\Entity\User;

/**
 * Auction
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\StoreBundle\Entity\AuctionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Auction
{
    use AuctionTrait;

    const DEFAULT_CUSTOM_PERCENTAGE     = 80;
    const DEFAULT_STORE_PERCENTAGE      = 50;

    const STATUS_ONBOARD                = 0;
    const STATUS_SOLD                   = 1;
    const STATUS_BACK_TO_STORE          = 10;
    const STATUS_ORDER_CANCEL           = 0;

    const PROFIT_STATUS_NOT_PAID_YET    = 0;
    const PROFIT_STATUS_PAY_COMPLETE    = 1;
    const PROFIT_STATUS_ASSIGN_COMPLETE = 2;

    protected $options;

    /**
     * @Exclude
     * @ORM\OneToOne(targetEntity="AuctionShipping", mappedBy="auction")
     * @var Shippings[]
     */
    protected $shipping;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="AuctionPayment", mappedBy="auction")
     * @var Payments[]
     */
    protected $payments;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\GoodsBundle\Entity\GoodsPassport", inversedBy="auctions")
     */
    private $product;

    /**
     * @var float
     *
     * @ORM\Column(name="custom_percentage", type="float")
     */
    private $customPercentage;

    /**
     * @var float
     *
     * @ORM\Column(name="store_percentage", type="float")
     */
    private $storePercentage;

    /**
     * @var float
     *
     * @ORM\Column(name="bso_percentage", type="float")
     */
    private $bsoPercentage;

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
     * @var integer
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    private $price;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="profit_status", type="integer")
     */
    private $profitStatus;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="auctions")
     */
    private $creater;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="auctions")
     */
    private $createStore;

    /**
     * @ORM\ManyToOne(targetEntity="Store", inversedBy="bsoAuctions")
     */
    private $bsoStore;

    /**
     * @ORM\ManyToOne(targetEntity="Woojin\OrderBundle\Entity\Custom", inversedBy="buyAuctions")
     */
    private $buyer;

    /**
     * @ORM\ManyToOne(targetEntity="Woojin\OrderBundle\Entity\Custom", inversedBy="sellAuctions")
     */
    private $seller;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="soldAt", type="datetime", nullable=true)
     */
    private $soldAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="paid_complete_at", type="datetime", nullable=true)
     */
    private $paidCompleteAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="assign_complete_at", type="datetime", nullable=true)
     */
    private $assignCompleteAt;

    /**
     * @ORM\ManyToOne(targetEntity="Woojin\UserBundle\Entity\User", inversedBy="backAuctions")
     */
    private $backer;

    /**
     * @ORM\ManyToOne(targetEntity="Woojin\UserBundle\Entity\User", inversedBy="cancelAuctions")
     */
    private $canceller;

    /**
     * @ORM\ManyToOne(targetEntity="Woojin\UserBundle\Entity\User", inversedBy="bssAuctions")
     */
    private $bsser;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $memo;

    /**
     * @var integer
     *
     * @ORM\Column(name="sau_count", type="integer", nullable=true)
     */
    protected $soldAtUpdateCount;

    /**
     * @var integer
     *
     * @ORM\Column(name="su_count", type="integer", nullable=true)
     */
    protected $soldUpdateCount;

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

    public function __construct(array $options = array())
    {
        $this->init($options);
    }

    protected function init(array $options)
    {
        $this->creater              = $options['creater'];
        $this->seller               = $options['seller'];
        $this->createStore          = $options['createStore'];
        $this->bsoStore             = $options['bsoStore'];
        $this->product              = $options['product'];
        $this->status               = $options['status'];
        $this->profitStatus         = 0;
        $this->customPercentage     = $options['customPercentage'];
        $this->storePercentage      = $options['storePercentage'];
        $this->bsoPercentage        = $options['bsoPercentage'];
    }

    public function back(array $options)
    {
        $this->status = self::STATUS_BACK_TO_STORE;
        $this->backer = $options['backer'];

        return $this;
    }

    public function sold(array $options)
    {
        return $this
            ->setStatus(Auction::STATUS_SOLD)
            ->setPrice($options['price'])
            ->setBsser($options['bsser'])
            ->setBuyer($options['buyer'])
            ->setSoldAt($options['soldAt'])
            ->patchSoldUpdateCount()
        ;
    }

    public function cancel(array $options)
    {
        $memo = "{$this->getMemo()}{$this->attachCancelMemo($options)}";

        return $this
            ->setStatus(self::STATUS_ORDER_CANCEL)
            ->setPrice(NULL)
            ->setBuyer(NULL)
            ->setCanceller($options['canceller'])
            ->setMemo($memo)
        ;
    }

    /**
     * Update soldAt
     *
     * @return $this
     */
    public function updateSoldAt(\DateTime $newDate, User $user)
    {
        $memo = "{$this->getMemo()}{$this->attachSoldAtUpdateMemo($newDate, $user)}";

        return $this
            ->setMemo($memo)
            ->setSoldAt($newDate)
            ->patchSoldAtUpdateCount()
        ;
    }

    protected function attachSoldAtUpdateMemo(\DateTime $newDate, User $user)
    {
        $date = new \DateTime();

        return "原售出時間:{$this->getSoldAtString()}，由{$user->getUsername()}於{$date->format('Y-m-d H:i:s')}更新為{$newDate->format('Y-m-d H:i:s')}<br/>";
    }

    protected function attachCancelMemo(array $options)
    {
        $date = new \DateTime();

        return "原售出金額:{$this->getPrice()}，購買人:{$this->getBuyerName()}，手機:{$this->getBuyerMobil()}，{$options['canceller']->getUsername()}於{$date->format('Y-m-d H:i:s')}取消<br/>";
    }

    public static function fetchProductStatusId($statusCode)
    {
        $map = array(
            static::STATUS_ONBOARD => Avenue::GS_BSO_ONBOARD,
            static::STATUS_SOLD => Avenue::GS_BSO_SOLD,
            static::STATUS_BACK_TO_STORE => Avenue::GS_ONSALE,
            static::STATUS_ORDER_CANCEL => Avenue::GS_BSO_ONBOARD
        );

        if (!array_key_exists($statusCode, $map)) {
            throw new \Exception('Not valid product status code found!');
        }

        return $map[$statusCode];
    }

    /**
     * Calculate the auction percentages for each by passing GoodsPassport entity
     *
     * @param  Woojin\GoodsBundle\Entity\GoodsPassport $product
     * @return array $percentages
     */
    public static function calculatePercentage(GoodsPassport $product)
    {
        $stages = array();
        $percentages = array();

        $stages[] = NULL === $product->getCustom() || false === $product->getIsAllowAuction() ? 0 : $product->getBsoCustomPercentage();
        $stages[] = true === $product->getIsAlanIn() ? 0 : self::DEFAULT_STORE_PERCENTAGE;

        $percentages[] = $stages[0];
        $percentages[] = (100 - $stages[0]) * $stages[1]/100;
        $percentages[] = 100 - $percentages[0] - $percentages[1];

        foreach ($percentages as $key => $val) {
            $percentages[$key] = $val/100;
        }

        return $percentages;
    }

    /**
     * Is auction belong to the given user's store
     *
     * @param  \Woojin\UserBundle\Entity\User  User    $user
     * @return boolean
     */
    public function isAuctionBelongGivenUsersStore(User $user)
    {
        if (NULL === $this->getBsoStore()) {
            return false;
        }

        return $this->getBsoStore()->getId() === $user->getStore()->getId();
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
     * Set customPercentage
     *
     * @param float $customPercentage
     *
     * @return Auction
     */
    public function setCustomPercentage($customPercentage)
    {
        $this->customPercentage = $customPercentage;

        return $this;
    }

    /**
     * Get customPercentage
     *
     * @return float
     */
    public function getCustomPercentage()
    {
        return $this->customPercentage;
    }

    /**
     * Set storePercentage
     *
     * @param float $storePercentage
     *
     * @return Auction
     */
    public function setStorePercentage($storePercentage)
    {
        $this->storePercentage = $storePercentage;

        return $this;
    }

    /**
     * Get storePercentage
     *
     * @return float
     */
    public function getStorePercentage()
    {
        return $this->storePercentage;
    }

    /**
     * Set bsoPercentage
     *
     * @param float $bsoPercentage
     *
     * @return Auction
     */
    public function setBsoPercentage($bsoPercentage)
    {
        $this->bsoPercentage = $bsoPercentage;

        return $this;
    }

    /**
     * Get bsoPercentage
     *
     * @return float
     */
    public function getBsoPercentage()
    {
        return $this->bsoPercentage;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     *
     * @return Auction
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
     * @return Auction
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
     * Set price
     *
     * @param integer $price
     *
     * @return Auction
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Auction
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
     * Set product
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $product
     *
     * @return Auction
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

    /**
     * Set creater
     *
     * @param \Woojin\UserBundle\Entity\User $creater
     *
     * @return Auction
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
     * Set createStore
     *
     * @param \Woojin\StoreBundle\Entity\Store $createStore
     *
     * @return Auction
     */
    public function setCreateStore(\Woojin\StoreBundle\Entity\Store $createStore = null)
    {
        $this->createStore = $createStore;

        return $this;
    }

    /**
     * Get createStore
     *
     * @return \Woojin\StoreBundle\Entity\Store
     */
    public function getCreateStore()
    {
        return $this->createStore;
    }

    /**
     * Set bsoStore
     *
     * @param \Woojin\StoreBundle\Entity\Store $bsoStore
     *
     * @return Auction
     */
    public function setBsoStore(\Woojin\StoreBundle\Entity\Store $bsoStore = null)
    {
        $this->bsoStore = $bsoStore;

        return $this;
    }

    /**
     * Get bsoStore
     *
     * @return \Woojin\StoreBundle\Entity\Store
     */
    public function getBsoStore()
    {
        return $this->bsoStore;
    }

    /**
     * Set buyer
     *
     * @param \Woojin\OrderBundle\Entity\Custom $buyer
     *
     * @return Auction
     */
    public function setBuyer(\Woojin\OrderBundle\Entity\Custom $buyer = null)
    {
        $this->buyer = $buyer;

        return $this;
    }

    /**
     * Get buyer
     *
     * @return \Woojin\OrderBundle\Entity\Custom
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * Set seller
     *
     * @param \Woojin\OrderBundle\Entity\Custom $seller
     *
     * @return Auction
     */
    public function setSeller(\Woojin\OrderBundle\Entity\Custom $seller = null)
    {
        $this->seller = $seller;

        return $this;
    }

    /**
     * Get seller
     *
     * @return \Woojin\OrderBundle\Entity\Custom
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * Set soldAt
     *
     * @param \DateTime $soldAt
     *
     * @return Auction
     */
    public function setSoldAt($soldAt)
    {
        $this->soldAt = $soldAt;

        return $this;
    }

    /**
     * Get soldAt
     *
     * @return \DateTime
     */
    public function getSoldAt()
    {
        return $this->soldAt;
    }

    /**
     * Set backer
     *
     * @param \Woojin\UserBundle\Entity\User $backer
     *
     * @return Auction
     */
    public function setBacker(\Woojin\UserBundle\Entity\User $backer = null)
    {
        $this->backer = $backer;

        return $this;
    }

    /**
     * Get backer
     *
     * @return \Woojin\UserBundle\Entity\User
     */
    public function getBacker()
    {
        return $this->backer;
    }

    /**
     * Set canceller
     *
     * @param \Woojin\UserBundle\Entity\User $canceller
     *
     * @return Auction
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
     * Set bsser
     *
     * @param \Woojin\UserBundle\Entity\User $bsser
     *
     * @return Auction
     */
    public function setBsser(\Woojin\UserBundle\Entity\User $bsser = null)
    {
        $this->bsser = $bsser;

        return $this;
    }

    /**
     * Get bsser
     *
     * @return \Woojin\UserBundle\Entity\User
     */
    public function getBsser()
    {
        return $this->bsser;
    }

    /**
     * Set memo
     *
     * @param string $memo
     *
     * @return Auction
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
    public function getMemo($isBr2n = false)
    {
        return true === $isBr2n ? str_replace(['<br>', '<br/>', '<br />'], "\r\n", $this->memo) : $this->memo;
    }

    /**
     * Set soldAtUpdateCount
     *
     * @param integer $soldAtUpdateCount
     *
     * @return Auction
     */
    public function setSoldAtUpdateCount($soldAtUpdateCount)
    {
        $this->soldAtUpdateCount = $soldAtUpdateCount;

        return $this;
    }

    /**
     * Get soldAtUpdateCount
     *
     * @return integer
     */
    public function getSoldAtUpdateCount()
    {
        return $this->soldAtUpdateCount;
    }

    public function patchSoldAtUpdateCount()
    {
        $this->soldAtUpdateCount = (NULL === $this->soldAtUpdateCount ? 0 : $this->soldAtUpdateCount) + 1;

        return $this;
    }

    /**
     * Set soldUpdateCount
     *
     * @param integer $soldUpdateCount
     *
     * @return Auction
     */
    public function setSoldUpdateCount($soldUpdateCount)
    {
        $this->soldUpdateCount = $soldUpdateCount;

        return $this;
    }

    /**
     * Get soldUpdateCount
     *
     * @return integer
     */
    public function getSoldUpdateCount()
    {
        return $this->soldUpdateCount;
    }

    public function patchSoldUpdateCount()
    {
        $this->soldUpdateCount = (NULL === $this->soldUpdateCount ? 0 : $this->soldUpdateCount) + 1;

        return $this;
    }

    /**
     * Set profitStatus
     *
     * @param integer $profitStatus
     *
     * @return Auction
     */
    public function setProfitStatus($profitStatus)
    {
        $this->profitStatus = $profitStatus;

        return $this;
    }

    /**
     * Get profitStatus
     *
     * @return integer
     */
    public function getProfitStatus()
    {
        return $this->profitStatus;
    }

    /**
     * Add payment
     *
     * @param \Woojin\StoreBundle\Entity\AuctionPayment $payment
     *
     * @return Auction
     */
    public function addPayment(\Woojin\StoreBundle\Entity\AuctionPayment $payment)
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Remove payment
     *
     * @param \Woojin\StoreBundle\Entity\AuctionPayment $payment
     */
    public function removePayment(\Woojin\StoreBundle\Entity\AuctionPayment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * Set paidCompleteAt
     *
     * @param \DateTime $paidCompleteAt
     *
     * @return Auction
     */
    public function setPaidCompleteAt($paidCompleteAt)
    {
        $this->paidCompleteAt = $paidCompleteAt;

        return $this;
    }

    /**
     * Get paidCompleteAt
     *
     * @return \DateTime
     */
    public function getPaidCompleteAt()
    {
        return $this->paidCompleteAt;
    }

    /**
     * Set assignCompleteAt
     *
     * @param \DateTime $assignCompleteAt
     *
     * @return Auction
     */
    public function setAssignCompleteAt($assignCompleteAt)
    {
        $this->assignCompleteAt = $assignCompleteAt;

        return $this;
    }

    /**
     * Get assignCompleteAt
     *
     * @return \DateTime
     */
    public function getAssignCompleteAt()
    {
        return $this->assignCompleteAt;
    }

    /**
     * Set shipping
     *
     * @param \Woojin\StoreBundle\Entity\AuctionShipping $shipping
     *
     * @return Auction
     */
    public function setShipping(\Woojin\StoreBundle\Entity\AuctionShipping $shipping = null)
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * Get shipping
     *
     * @return \Woojin\StoreBundle\Entity\AuctionShipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }
}

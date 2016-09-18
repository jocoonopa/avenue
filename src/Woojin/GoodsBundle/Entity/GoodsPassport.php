<?php 

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

use Woojin\UserBundle\Entity\User;
use Woojin\Utility\Avenue\Avenue;

/**
 * @ORM\Entity(repositoryClass="Woojin\GoodsBundle\Entity\GoodsPassportRepository")
 * @ORM\Table(name="goods_passport")
 * @ORM\HasLifecycleCallbacks()
 */
class GoodsPassport
{   
    /**
     * @ORM\ManyToOne(targetEntity="SeoSlogan", inversedBy="products")
     * @var SeoSlogan
     */
    protected $seoSlogan;

    /**
     * @ORM\ManyToOne(targetEntity="SeoSlogan", inversedBy="products2")
     * @var SeoSlogan2
     */
    protected $seoSlogan2;

   /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Behalf", mappedBy="want")
     * @var WantBehalfs[]
     */
    protected $wantBehalfs;

    /**
     * @ORM\OneToOne(targetEntity="Behalf", inversedBy="got")
     * @ORM\JoinColumn(name="gotBehalf_id", referencedColumnName="id")
     **/
    private $gotBehalf;

    /**
     * @ORM\OneToOne(targetEntity="ProductTl", inversedBy="product")
     * @ORM\JoinColumn(name="productTl_id", referencedColumnName="id")
     **/
    private $productTl;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\StoreBundle\Entity\Auction", mappedBy="product")
     * @var Auctions[]
     **/
    private $auctions;

    /**
    * @ORM\ManyToOne(targetEntity="\Woojin\GoodsBundle\Entity\Desimg", inversedBy="goodsPassports")
    * @var Desimg
    */
    protected $desimg;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\GoodsBundle\Entity\Description", inversedBy="goodsPassports")
     * @var Description
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\GoodsBundle\Entity\Brief", inversedBy="goodsPassports")
     * @var Brief
     */
    protected $brief;

    /**
     * @ORM\ManyToMany(targetEntity="Category")
     * @ORM\JoinTable(name="GoodsPassport_Category",
     *      joinColumns={@ORM\JoinColumn(name="category_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="goods_passport_id", referencedColumnName="id")}
     * )
     **/
    private $categorys;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\StoreBundle\Entity\Store", inversedBy="goodsPassports")
     * @var Store
     */
    protected $store;

    /**
     * @ORM\ManyToOne(targetEntity="Promotion", inversedBy="goodsPassports")
     * @var Promotion
     */
    protected $promotion;

    /**
     * @ORM\ManyToOne(targetEntity="Brand", inversedBy="goodsPassports")
     * @var Brand
     */
    protected $brand;

    /**
     * @ORM\ManyToOne(targetEntity="Color", inversedBy="goodsPassports")
     * @var Color
     */
    protected $color;

    /**
     * @ORM\ManyToOne(targetEntity="Pattern", inversedBy="goodsPassports")
     * @var Pattern
     */
    protected $pattern;

    /**
     * @Exclude
     * @ORM\ManyToOne(targetEntity="\Woojin\OrderBundle\Entity\Custom", inversedBy="goodsPassports")
     * @var Custom
     */
    protected $custom;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\OrderBundle\Entity\Orders", mappedBy="goods_passport", orphanRemoval=true)
     * @var Orders[]
     */
    protected $orders;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Move", mappedBy="orgGoods", orphanRemoval=true)
     * @var OrgMoves[]
     */
    protected $orgMoves;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Move", mappedBy="newGoods", orphanRemoval=true)
     * @var newMoves[]
     */
    protected $newMoves;

    /**
     * @var Parent
     * @Exclude
     * @ORM\ManyToOne(targetEntity="GoodsPassport", inversedBy="inherits", cascade={"persist"})
     * @ORM\JoinColumn(name="inherit_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @var Inherits[]
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="parent")
     */
    protected $inherits;


  /**
   * @ORM\ManyToOne(targetEntity="GoodsStatus", inversedBy="goods_passports")
   * @var Status
   */
  protected $status;

   /**
   * @ORM\ManyToOne(targetEntity="GoodsSource", inversedBy="goods_passport")
   * @var Source
   */
  protected $source;

  /**
   * @ORM\ManyToOne(targetEntity="Img", inversedBy="goods_passport")
   * @var Img
   */
  protected $img;

  /**
   * @ORM\ManyToOne(targetEntity="\Woojin\StoreBundle\Entity\Activity", inversedBy="goods_passport")
   * @var Activity
   */
  protected $activity;

  /**
   * @ORM\ManyToOne(targetEntity="GoodsMT", inversedBy="goods_passports")
   * @var GoodsMT
   */
  protected $mt;

  /**
   * @ORM\ManyToOne(targetEntity="GoodsLevel", inversedBy="goods_passports")
   * @var Level
   */
  protected $level;

  /**
   * @Exclude
   * @ORM\OneToMany(targetEntity="\Woojin\StoreBundle\Entity\StockTakeRecord", mappedBy="goods_passport")
   * @var StockTakeRecord[]
   */
  protected $stock_take_record;

  /**
   * @ORM\Id
   * @ORM\Column(type="integer")
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  protected $id;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  protected $sn;

  /**
   * @ORM\Column(type="string", nullable=true)
   */
  protected $name;

  /**
   * @ORM\Column(type="integer", length=10)
   */
  protected $cost;

  /**
   * @ORM\Column(type="integer")
   */
  protected $price;

  /**
   * @ORM\Column(type="integer")
   */
  protected $webPrice;

  /**
   * @ORM\Column(type="string", length=100, nullable=true)
   */
  protected $org_sn;

  /**
   * @ORM\Column(type="text", nullable=true)
   */
  protected $memo;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  protected $colorSn;

  /**
   * @ORM\Column(type="datetime", length=50, nullable=true)
   */
  protected $created_at;

  /**
   * @ORM\Column(type="datetime", length=50, nullable=true)
   */
  protected $updateAt;

  /**
   * @ORM\Column(type="string", length=50, nullable=true)
   */
  protected $model;

  /**
   * @ORM\Column(type="string", length=10, nullable=true)
   */
  protected $customSn;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $isAllowWeb;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $isAllowCreditCard;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $isBehalf;

  /**
   * @ORM\Column(type="string", length=40, nullable=true)
   */
  protected $yahooId;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $isAllowAuction;

  /**
   * @ORM\Column(type="boolean")
   */
  protected $isAlanIn;

  /**
   * @ORM\Column(type="string", length=40, nullable=true)
   */
  protected $seoWord;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->orgMoves = new \Doctrine\Common\Collections\ArrayCollection();
        $this->newMoves = new \Doctrine\Common\Collections\ArrayCollection();
        $this->inherits = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stock_take_record = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isAllowWeb = false;
        $this->webPrice = 0;
        $this->isBehalf = false;
        $this->isAllowCreditCard = true;
        $this->isAllowAuction = false;
    }

    /**
     * 檢查是否為代購商品
     * 
     * @return boolean
     */
    public function isBehalfAndSold()
    {
        return (in_array($this->status->getId(), array(Avenue::GS_SOLDOUT, Avenue::GS_BEHALF)) 
            && ($this->isBehalf));
    }

    /**
     * 取得符合 SEO 的商品名稱
     * 
     * @return string
     */
    public function getSeoName()
    {
        $seoName = '';

        if ($this->getBrand()) {
            $seoName .= $this->getBrand()->getName() . ' ';
        }

        $seoName .= trim($this->getName());

        if ($this->getSeoSlogan()) {
            $seoName .= $this->getSeoSlogan()->getName() . ' ';
        }

        if ($this->getSeoSlogan2()) {
            $seoName .= $this->getSeoSlogan2()->getName() . ' ';
        }

        $seoName .= $this->getSeoWord() . ' ';

        $seoName .= str_replace(array('無型號', '無'), array('', ''), $this->getModel()) . ' ';

        if ($this->getLevel()) {
            $seoName .= ' ' . $this->getLevel()->getConvertName();   
        }

        return $seoName;
    }

    /**
     * 取得合法的出售訂單，匯出毛利報表時可用
     * 
     * @return \Woojin\OrderBundle\Entity\Orders $order [存在時]
     *         boolean                           null   [不存在時]
     */
    public function getValidOutOrder()
    {
        foreach ($this->orders as $order) {
            if ($order->getKind()->getType() === Avenue::OKT_OUT 
                && $order->getStatus()->getId() === Avenue::OS_COMPLETE
            ) {
                return $order;
            }
        }

        return null;
    }

    /**
     * 取得官網顯示價格
     *
     * 2015-03-03
     * 1. 滿額贈模式
     *  -> 累計
     *  -> 不累計(單次)
     *  -> 商品獨自計算
     *  -> 滿額贈不可和折扣並用
     * 
     * ======== flow =========
     *
     * 如果不允許網路顯示，直接返回 null
     *
     * 如果網路售價合法[大於等於 100，且小於原始售價]
     *     返回網路售價
     *
     * 若否
     *     存在活動且活動折扣 < 1
     *         返回原始售價 * 活動折扣金額
     *     若否
     *         返回 null
     * 
     * ======= End Flow =======
     *     
     * @return integer | boolean
     */
    public function getPromotionPrice($price = null)
    {
        if (!$this->isAllowWeb) {
            return null;
        }

        if ($this->productTl &&  $this->productTl->getPrice() >= 100 && $this->productTl->isValid()) {
            return $this->productTl->getPrice();
        }

        // 如果本商品屬於促銷活動，且該促銷活動有效
        if ($this->promotion && $this->promotion->isValid()) {
            $displayPrice = (($this->webPrice && $this->webPrice >= 100) ? $this->webPrice : $this->price);
            // 根據贈送金額是否大於0判斷是否為滿額贈活動
            if ($this->promotion->getGift() > 0) {
                // 售價若大於滿額贈門檻
                if ($displayPrice >= $this->promotion->getThread()) {
                    return ($this->promotion->getIsStack()) // 累計模式 或是單次模式
                        ? $displayPrice - ($this->promotion->getGift() * floor($displayPrice/$this->promotion->getThread()))
                        : $displayPrice - $this->promotion->getGift()
                    ;
                }
            }

            if ($this->promotion->getDiscount() < 1) {
                return ($displayPrice * $this->promotion->getDiscount());
            }
        }

        if ($this->webPrice && ($this->webPrice >= 100)) {
            return $this->webPrice;
        }

        if ($price) {
            return $this->price;
        }
    }

    /**
     * 官網是否可以顯示的判斷
     * 
     * @return boolean
     */
    public function isValidToShow()
    {
        return ($this->isAllowWeb 
            && (
                in_array($this->status->getId(), array(Avenue::GS_ONSALE, Avenue::GS_ACTIVITY))
                || (in_array($this->status->getId(), array(Avenue::GS_SOLDOUT, Avenue::GS_BEHALF)) &&$this->isBehalf)
            )
        );
    }

    /**
     * Check whether the product status is on board or not
     * 
     * @return boolean         
     */
    public function isProductOnSale()
    {
        return Avenue::GS_ONSALE === $this->getStatus()->getId();
    }

    /**
     * Is product status_id equal to Avenue::GS_BSO_ONBOARD?
     * 
     * @return boolean         
     */
    public function isProductBsoOnBoard()
    {
        return Avenue::GS_BSO_ONBOARD === $this->getStatus()->getId();
    }

    /**
     * Is product status_id equal to Avenue::GS_BSO_SOLD
     * 
     * @return boolean              
     */
    public function isProductBsoSold()
    {
        return Avenue::GS_BSO_SOLD === $this->getStatus()->getId();
    }

    /**
     * isOwnProduct 的簡短版
     * 
     * @param  User    $user
     * @return boolean      
     */
    public function isOwn(User $user)
    {
        return $this->isOwnProduct($user);
    } 

    /**
     * 檢查該商品是否為所屬店的商品
     * 
     * @param  User    $user
     * @return boolean      
     */
    public function isOwnProduct(User $user)
    {
        return $user->getStore()->getSn() === substr($this->getSn(), 0, 1);
    }

    /**
     * 檢查商品是否已經同不到Yahoo商城
     * 
     * @return boolean
     */
    public function isYahooProduct()
    {
        return NULL !== $this->getYahooId();
    }

    /**
     * 取得Uitox商品規格資訊
     * 
     * @return string
     */
    public function getSpec()
    {
        // $spec = null;

        // $spec .= '商品品牌：' . (($this->brand) ? $this->brand->getName() : null);
        // $spec .= "\n";
        // $spec .= '商品材質：' . (($this->mt) ? $this->mt->getName() : null);
        // $spec .= "\n";
        // $spec .= '商品新舊：' . (($this->level) ? $this->level->getConvertName() . ' / 約' . $this->level->getName() : null);
        // $spec .= "\n";
        // $spec .= '商品顏色：' . (($this->color) ? $this->color->getName() : null);
        // $spec .= "\n";
        // $spec .= '商品尺寸：';
        // $spec .= '商品配件： 本店購買憑證';
        // $spec .= "\n";
        // $spec .= '※圖片顏色會因拍攝燈光或個人螢幕設定差異，造成部份色差現象，請以實際商品顏色為準。
        // (鑑賞期間如需退換貨,請保持商品包裝的完整性,且須全新未使用及手把膠膜與拆封條未破壞狀態
        // (包含商品.附件.內外包裝.隨機文件等..均務必保持完整齊全)';

        // return $spec;
        return $this->description->getContent();
    }

    /**
     * 搭配權限檢查後回傳的成本，若是判斷權限不足預設回傳無閱讀權限
     * 
     * @param  User   $user
     * @return integer $cost [權限合法時]
     *         string $msg [權限不合法時]      
     */
    public function getCostVerifyed(User $user, $msg = '無閱讀權限')
    {
        return (
            ($this->isOwnProduct($user) && $user->getRole()->hasAuth('READ_COST_OWN'))
            || $user->getRole()->hasAuth('READ_COST_ALL')
        )
            ? $this->getCost()
            : $msg
        ;
    }

    /**
     * 取得寄賣回扣(一般來說就是成本，此找法是透過訂單查找準確性會更高但較耗效能)
     * 
     * @return integer [存在寄賣回扣]
     *         null [不存在寄賣回扣]
     */
    public function getFeedBack()
    {
        if ($order = $this->getFeedBackOrder()) {
            return $order->getRequired();
        }

        return null;
    }

    /**
     * 解決取得寄賣訂單的麻煩
     */
    public function getFeedBackOrder()
    {
        foreach ($this->orders as $order) {
            if ($order->getKind()->getId() === Avenue::OK_FEEDBACK) {
                return $order;
            }
        } 

        return false;
    }

    /**
     * 取得該商品最後身分
     */
    public function getCurrent()
    {
        return ($this->inherits[(count($this->inherits) - 1)]);
    }

    /**
     * 產生商品產編，因為店碼現在很難判斷一定就是更改者所屬店，因此必須當做開放引數處理
     * 
     * @param  string $storeSn
     * @return string         
     */
    public function genSn($storeSn) 
    {
        $sn = null;
        $id = ($this->id > 9999) ? substr($this->id, 1) : str_pad($this->id, 4, 0, STR_PAD_LEFT);
        $cost = str_pad($this->cost/Avenue::SN_RATE, 4, 0, STR_PAD_LEFT);
        $price = str_pad($this->price/Avenue::SN_RATE, 4, 0, STR_PAD_LEFT);
        
        for ($i = 0; $i < 4; $i ++) {
            if ($this->id > 9999) {
                $sn .= substr($id, 3 - $i, 1) . substr($cost, $i, 1);
            } else {
                $sn .= substr($id, $i, 1) . substr($cost, $i, 1);
            }   
        }

        return $storeSn . $sn . $price;
    }   

    /**
     * @ORM\PrePersist
     */
    public function autoSetCreatedAt()
    {
        $this->setCreatedAt(new \Datetime());
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function autoSetUpdateAt()
    {
        $this->setUpdateAt(new \Datetime());
    }

    public function getImgName(UploadedFile $file, $prefix = 'main')
    {
        return $prefix . '_' . $this->id .  '.' . $file->getClientOriginalExtension();
    }

    public function getImgRelDir(User $user)
    {
        $dirParts = array();

        $dirParts[] = 'img';
        $dirParts[] = 'product';
        $dirParts[] = $user->getStore()->getSn();
        $dirParts[] = ($this->brand) ? $this->brand->getName() : 'NoBrand';
        $dirParts[] = ($this->pattern) ? $this->pattern->getName() : 'NoPattern';
        $dirParts[] = $this->sn;

        return '/' . implode('/', $dirParts);
    }

    /**
     * 製造歐付寶需要的商品陣列
     * 
     * @return array
     */
    public function genItem()
    {
        return array(
            'Name' => $this->getName(),
            'Price' => (int) $this->getPromotionPrice(true),
            'Currency' => '元',
            'Quantity' => 1,
            'URL' => ''
        );
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
     * Set sn
     *
     * @param string $sn
     * @return GoodsPassport
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
     * Set name
     *
     * @param string $name
     * @return GoodsPassport
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @param boolean $isOrigin
     * @return string 
     */
    public function getName($isOrigin = false)
    {
        return true === $isOrigin ? $this->name : strtoupper($this->name);
    }

    /**
     * Set cost
     *
     * @param integer $cost
     * @return GoodsPassport
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    
        return $this;
    }

    /**
     * Get cost
     *
     * @return integer 
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set price
     *
     * @param integer $price
     * @return GoodsPassport
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
     * Set org_sn
     *
     * @param string $orgSn
     * @return GoodsPassport
     */
    public function setOrgSn($orgSn)
    {
        $this->org_sn = trim($orgSn);
    
        return $this;
    }

    /**
     * Get org_sn
     *
     * @return string 
     */
    public function getOrgSn()
    {
        return $this->org_sn;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return GoodsPassport
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
     * Set colorSn
     *
     * @param string $colorSn
     * @return GoodsPassport
     */
    public function setColorSn($colorSn)
    {
        $this->colorSn = $colorSn;
    
        return $this;
    }

    /**
     * Get colorSn
     *
     * @return string 
     */
    public function getColorSn()
    {
        return $this->colorSn;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return GoodsPassport
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set model
     *
     * @param string $model
     * @return GoodsPassport
     */
    public function setModel($model)
    {
        $this->model = trim($model);
    
        return $this;
    }

    /**
     * Get model
     *
     * @return string 
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set customSn
     *
     * @param string $customSn
     * @return GoodsPassport
     */
    public function setCustomSn($customSn)
    {
        $this->customSn = $customSn;
    
        return $this;
    }

    /**
     * Get customSn
     *
     * @return string 
     */
    public function getCustomSn()
    {
        return $this->customSn;
    }

    /**
     * Set brand
     *
     * @param \Woojin\GoodsBundle\Entity\Brand $brand
     * @return GoodsPassport
     */
    public function setBrand(\Woojin\GoodsBundle\Entity\Brand $brand = null)
    {
        $this->brand = $brand;
    
        return $this;
    }

    /**
     * Get brand
     *
     * @return \Woojin\GoodsBundle\Entity\Brand 
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Set color
     *
     * @param \Woojin\GoodsBundle\Entity\Color $color
     * @return GoodsPassport
     */
    public function setColor(\Woojin\GoodsBundle\Entity\Color $color = null)
    {
        $this->color = $color;
    
        return $this;
    }

    /**
     * Get color
     *
     * @return \Woojin\GoodsBundle\Entity\Color 
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set pattern
     *
     * @param \Woojin\GoodsBundle\Entity\Pattern $pattern
     * @return GoodsPassport
     */
    public function setPattern(\Woojin\GoodsBundle\Entity\Pattern $pattern = null)
    {
        $this->pattern = $pattern;
    
        return $this;
    }

    /**
     * Get pattern
     *
     * @return \Woojin\GoodsBundle\Entity\Pattern 
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Set custom
     *
     * @param \Woojin\OrderBundle\Entity\Custom $custom
     * @return GoodsPassport
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
     * Add orders
     *
     * @param \Woojin\OrderBundle\Entity\Orders $orders
     * @return GoodsPassport
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
     * Add orgMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $orgMoves
     * @return GoodsPassport
     */
    public function addOrgMove(\Woojin\GoodsBundle\Entity\Move $orgMoves)
    {
        $this->orgMoves[] = $orgMoves;
    
        return $this;
    }

    /**
     * Remove orgMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $orgMoves
     */
    public function removeOrgMove(\Woojin\GoodsBundle\Entity\Move $orgMoves)
    {
        $this->orgMoves->removeElement($orgMoves);
    }

    /**
     * Get orgMoves
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrgMoves()
    {
        return $this->orgMoves;
    }

    /**
     * Add newMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $newMoves
     * @return GoodsPassport
     */
    public function addNewMove(\Woojin\GoodsBundle\Entity\Move $newMoves)
    {
        $this->newMoves[] = $newMoves;
    
        return $this;
    }

    /**
     * Remove newMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $newMoves
     */
    public function removeNewMove(\Woojin\GoodsBundle\Entity\Move $newMoves)
    {
        $this->newMoves->removeElement($newMoves);
    }

    /**
     * Get newMoves
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNewMoves()
    {
        return $this->newMoves;
    }

    /**
     * Set parent
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $parent
     * @return GoodsPassport
     */
    public function setParent(\Woojin\GoodsBundle\Entity\GoodsPassport $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsPassport 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add inherits
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $inherits
     * @return GoodsPassport
     */
    public function addInherit(\Woojin\GoodsBundle\Entity\GoodsPassport $inherits)
    {
        $this->inherits[] = $inherits;
    
        return $this;
    }

    /**
     * Remove inherits
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $inherits
     */
    public function removeInherit(\Woojin\GoodsBundle\Entity\GoodsPassport $inherits)
    {
        $this->inherits->removeElement($inherits);
    }

    /**
     * Get inherits
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInherits()
    {
        return $this->inherits;
    }

    /**
     * Set status
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsStatus $status
     * @return GoodsPassport
     */
    public function setStatus(\Woojin\GoodsBundle\Entity\GoodsStatus $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set source
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsSource $source
     * @return GoodsPassport
     */
    public function setSource(\Woojin\GoodsBundle\Entity\GoodsSource $source = null)
    {
        $this->source = $source;
    
        return $this;
    }

    /**
     * Get source
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsSource 
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set img
     *
     * @param \Woojin\GoodsBundle\Entity\Img $img
     * @return GoodsPassport
     */
    public function setImg(\Woojin\GoodsBundle\Entity\Img $img = null)
    {
        $this->img = $img;
    
        return $this;
    }

    /**
     * Get img
     *
     * @return \Woojin\GoodsBundle\Entity\Img 
     */
    public function getImg()
    {
        return $this->img;
    }

    /**
     * Set activity
     *
     * @param \Woojin\StoreBundle\Entity\Activity $activity
     * @return GoodsPassport
     */
    public function setActivity(\Woojin\StoreBundle\Entity\Activity $activity = null)
    {
        $this->activity = $activity;
    
        return $this;
    }

    /**
     * Get activity
     *
     * @return \Woojin\StoreBundle\Entity\Activity 
     */
    public function getActivity()
    {
        return $this->activity;
    }

    /**
     * Set mt
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsMT $mt
     * @return GoodsPassport
     */
    public function setMt(\Woojin\GoodsBundle\Entity\GoodsMT $mt = null)
    {
        $this->mt = $mt;
    
        return $this;
    }

    /**
     * Get mt
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsMT 
     */
    public function getMt()
    {
        return $this->mt;
    }

    /**
     * Set level
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsLevel $level
     * @return GoodsPassport
     */
    public function setLevel(\Woojin\GoodsBundle\Entity\GoodsLevel $level = null)
    {
        $this->level = $level;
    
        return $this;
    }

    /**
     * Get level
     *
     * @return \Woojin\GoodsBundle\Entity\GoodsLevel 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Add stock_take_record
     *
     * @param \Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord
     * @return GoodsPassport
     */
    public function addStockTakeRecord(\Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord)
    {
        $this->stock_take_record[] = $stockTakeRecord;
    
        return $this;
    }

    /**
     * Remove stock_take_record
     *
     * @param \Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord
     */
    public function removeStockTakeRecord(\Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord)
    {
        $this->stock_take_record->removeElement($stockTakeRecord);
    }

    /**
     * Get stock_take_record
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getStockTakeRecord()
    {
        return $this->stock_take_record;
    }

    public function hasCategory(\Woojin\GoodsBundle\Entity\Category $category)
    {
        $ids = array();

        foreach ($this->categorys as $eachCat) {
            $ids[] = $eachCat->getId();
        }

        return (in_array($category->getId(), $ids));
    }

    /**
     * Add categorys
     *
     * @param \Woojin\GoodsBundle\Entity\Category $categorys
     * @return GoodsPassport
     */
    public function addCategory(\Woojin\GoodsBundle\Entity\Category $categorys)
    {
        $this->categorys[] = $categorys;
    
        return $this;
    }

    /**
     * Remove categorys
     *
     * @param \Woojin\GoodsBundle\Entity\Category $categorys
     */
    public function removeCategory(\Woojin\GoodsBundle\Entity\Category $categorys)
    {
        $this->categorys->removeElement($categorys);
    }

    /**
     * Get categorys
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategorys()
    {
        return $this->categorys;
    }

    /**
     * Set promotion
     *
     * @param \Woojin\GoodsBundle\Entity\Promotion $promotion
     * @return GoodsPassport
     */
    public function setPromotion(\Woojin\GoodsBundle\Entity\Promotion $promotion = null)
    {
        $this->promotion = $promotion;
    
        return $this;
    }

    /**
     * Get promotion
     *
     * @return \Woojin\GoodsBundle\Entity\Promotion 
     */
    public function getPromotion()
    {
        return $this->promotion;
    }

    /**
     * Set updateAt
     *
     * @param \DateTime $updateAt
     * @return GoodsPassport
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
     * Set store
     *
     * @param \Woojin\StoreBundle\Entity\Store $store
     * @return GoodsPassport
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
     * Set desimg
     *
     * @param \Woojin\GoodsBundle\Entity\Desimg $desimg
     * @return GoodsPassport
     */
    public function setDesimg(\Woojin\GoodsBundle\Entity\Desimg $desimg = null)
    {
        $this->desimg = $desimg;
    
        return $this;
    }

    /**
     * Get desimg
     *
     * @return \Woojin\GoodsBundle\Entity\Desimg 
     */
    public function getDesimg()
    {
        return $this->desimg;
    }

    /**
     * Set description
     *
     * @param \Woojin\GoodsBundle\Entity\Description $description
     * @return GoodsPassport
     */
    public function setDescription(\Woojin\GoodsBundle\Entity\Description $description = null)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return \Woojin\GoodsBundle\Entity\Description 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set brief
     *
     * @param \Woojin\GoodsBundle\Entity\Brief $brief
     * @return GoodsPassport
     */
    public function setBrief(\Woojin\GoodsBundle\Entity\Brief $brief = null)
    {
        $this->brief = $brief;
    
        return $this;
    }

    /**
     * Get brief
     *
     * @return \Woojin\GoodsBundle\Entity\Brief 
     */
    public function getBrief()
    {
        return $this->brief;
    }

    /**
     * Set isAllowWeb
     *
     * @param boolean $isAllowWeb
     * @return GoodsPassport
     */
    public function setIsAllowWeb($isAllowWeb)
    {
        if ($isAllowWeb === 1) {
            $isAllowWeb = true;
        }

        if ($isAllowWeb === 0) {
            $isAllowWeb = false;
        }

        $this->isAllowWeb = $isAllowWeb;
    
        return $this;
    }

    /**
     * Get isAllowWeb
     *
     * @return boolean 
     */
    public function getIsAllowWeb()
    {
        return $this->isAllowWeb;
    }

    /**
     * Set webPrice
     *
     * @param integer $webPrice
     * @return GoodsPassport
     */
    public function setWebPrice($webPrice)
    {
        $this->webPrice = $webPrice;
    
        return $this;
    }

    /**
     * Get webPrice
     *
     * @return integer 
     */
    public function getWebPrice()
    {
        return $this->webPrice;
    }

    /**
     * Set productTl
     *
     * @param \Woojin\GoodsBundle\Entity\ProductTl $productTl
     * @return GoodsPassport
     */
    public function setProductTl(\Woojin\GoodsBundle\Entity\ProductTl $productTl = null)
    {
        $this->productTl = $productTl;
    
        return $this;
    }

    /**
     * Get productTl
     *
     * @return \Woojin\GoodsBundle\Entity\ProductTl 
     */
    public function getProductTl()
    {
        return $this->productTl;
    }

    /**
     * Set isBehalf
     *
     * @param boolean $isBehalf
     * @return GoodsPassport
     */
    public function setIsBehalf($isBehalf)
    {
        if ($isBehalf === 1) {
            $isBehalf = true;
        }

        if ($isBehalf === 0) {
            $isBehalf = false;
        }

        $this->isBehalf = $isBehalf;
    
        return $this;
    }

    /**
     * Get isBehalf
     *
     * @return boolean 
     */
    public function getIsBehalf()
    {
        return $this->isBehalf;
    }

    /**
     * Set wantBehalf
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $wantBehalf
     * @return GoodsPassport
     */
    public function setWantBehalf(\Woojin\GoodsBundle\Entity\Behalf $wantBehalf = null)
    {
        $this->wantBehalf = $wantBehalf;
    
        return $this;
    }

    /**
     * Get wantBehalf
     *
     * @return \Woojin\GoodsBundle\Entity\Behalf 
     */
    public function getWantBehalf()
    {
        return $this->wantBehalf;
    }

    /**
     * Set gotBehalf
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $gotBehalf
     * @return GoodsPassport
     */
    public function setGotBehalf(\Woojin\GoodsBundle\Entity\Behalf $gotBehalf = null)
    {
        $this->gotBehalf = $gotBehalf;
    
        return $this;
    }

    /**
     * Get gotBehalf
     *
     * @return \Woojin\GoodsBundle\Entity\Behalf 
     */
    public function getGotBehalf()
    {
        return $this->gotBehalf;
    }

    /**
     * Add wantBehalfs
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $wantBehalfs
     * @return GoodsPassport
     */
    public function addWantBehalf(\Woojin\GoodsBundle\Entity\Behalf $wantBehalfs)
    {
        $this->wantBehalfs[] = $wantBehalfs;
    
        return $this;
    }

    /**
     * Remove wantBehalfs
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $wantBehalfs
     */
    public function removeWantBehalf(\Woojin\GoodsBundle\Entity\Behalf $wantBehalfs)
    {
        $this->wantBehalfs->removeElement($wantBehalfs);
    }

    /**
     * Get wantBehalfs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWantBehalfs()
    {
        return $this->wantBehalfs;
    }

    /**
     * Set yahooId
     *
     * @param string $yahooId
     * @return GoodsPassport
     */
    public function setYahooId($yahooId)
    {
        $this->yahooId = $yahooId;
    
        return $this;
    }

    /**
     * Get yahooId
     *
     * @return string 
     */
    public function getYahooId()
    {
        return $this->yahooId;
    }

    /**
     * Set seoWord
     *
     * @param string $seoWord
     * @return GoodsPassport
     */
    public function setSeoWord($seoWord)
    {
        $this->seoWord = $seoWord;
    
        return $this;
    }

    /**
     * Get seoWord
     *
     * @return string 
     */
    public function getSeoWord()
    {
        return $this->seoWord;
    }

    /**
     * Set seoslogan
     *
     * @param \Woojin\GoodsBundle\Entity\SeoSlogan $seoslogan
     * @return GoodsPassport
     */
    public function setSeoSlogan(\Woojin\GoodsBundle\Entity\SeoSlogan $seoslogan = null)
    {
        $this->seoSlogan = $seoslogan;
    
        return $this;
    }

    /**
     * Get seoslogan
     *
     * @return \Woojin\GoodsBundle\Entity\SeoSlogan 
     */
    public function getSeoSlogan()
    {
        return $this->seoSlogan;
    }

    /**
     * Set seoSlogan2
     *
     * @param \Woojin\GoodsBundle\Entity\SeoSlogan $seoSlogan2
     * @return GoodsPassport
     */
    public function setSeoSlogan2(\Woojin\GoodsBundle\Entity\SeoSlogan $seoSlogan2 = null)
    {
        $this->seoSlogan2 = $seoSlogan2;
    
        return $this;
    }

    /**
     * Get seoSlogan2
     *
     * @return \Woojin\GoodsBundle\Entity\SeoSlogan 
     */
    public function getSeoSlogan2()
    {
        return $this->seoSlogan2;
    }

    /**
     * Set isAllowCreditCard
     *
     * @param boolean $isAllowCreditCard
     * @return GoodsPassport
     */
    public function setIsAllowCreditCard($isAllowCreditCard)
    {
        if ($isAllowCreditCard === 1) {
            $isAllowCreditCard = true;
        }

        if ($isAllowCreditCard === 0) {
            $isAllowCreditCard = false;
        }

        $this->isAllowCreditCard = $isAllowCreditCard;
    
        return $this;
    }

    /**
     * Get isAllowCreditCard
     *
     * @return boolean 
     */
    public function getIsAllowCreditCard()
    {
        return $this->isAllowCreditCard;
    }

    /**
     * Set isAllowAuction
     *
     * @param boolean $isAllowAuction
     *
     * @return GoodsPassport
     */
    public function setIsAllowAuction($isAllowAuction)
    {
        $this->isAllowAuction = $isAllowAuction;

        return $this;
    }

    /**
     * Get isAllowAuction
     *
     * @return boolean
     */
    public function getIsAllowAuction()
    {
        return $this->isAllowAuction;
    }

    /**
     * Add auction
     *
     * @param \Woojin\StoreBundle\Entity\Auction $auction
     *
     * @return GoodsPassport
     */
    public function addAuction(\Woojin\StoreBundle\Entity\Auction $auction)
    {
        $this->auctions[] = $auction;

        return $this;
    }

    /**
     * Remove auction
     *
     * @param \Woojin\StoreBundle\Entity\Auction $auction
     */
    public function removeAuction(\Woojin\StoreBundle\Entity\Auction $auction)
    {
        $this->auctions->removeElement($auction);
    }

    /**
     * Get auctions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuctions()
    {
        return $this->auctions;
    }

    /**
     * Add bsoAuction
     *
     * @param \Woojin\StoreBundle\Entity\Auction $bsoAuction
     *
     * @return GoodsPassport
     */
    public function addBsoAuction(\Woojin\StoreBundle\Entity\Auction $bsoAuction)
    {
        $this->bsoAuctions[] = $bsoAuction;

        return $this;
    }

    /**
     * Set isAlanIn
     *
     * @param boolean $isAlanIn
     *
     * @return GoodsPassport
     */
    public function setIsAlanIn($isAlanIn)
    {
        $this->isAlanIn = $isAlanIn;

        return $this;
    }

    /**
     * Get isAlanIn
     *
     * @return boolean
     */
    public function getIsAlanIn()
    {
        return $this->isAlanIn;
    }
}

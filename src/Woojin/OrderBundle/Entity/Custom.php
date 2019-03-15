<?php
namespace Woojin\OrderBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use Woojin\AgencyBundle\Entity\AgencyObject;
use Symfony\Component\Validator\Constraints as Assert;
use Woojin\Utility\Handler\FacebookResponseHandler;
use Woojin\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Woojin\OrderBundle\Entity\CustomRepository")
 * @ORM\Table(name="custom")
 * @ORM\HasLifecycleCallbacks()
 */
class Custom
{
    const SALT = 'jasodi12n3!!$#';
    const DEFAULT_PASSWORD = '000USELESS111';

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Benefit", mappedBy="custom")
     * @var Benefits[]
     */
    protected $benefits;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\Behalf", mappedBy="custom")
     * @var Behalfs[]
     */
    protected $behalfs;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="CustomOpe", mappedBy="custom")
     * @var CustomOpes[]
     */
    protected $customOpes;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\GoodsPassport", mappedBy="custom")
     * @var GoodsPassports[]
     */
    protected $goodsPassports;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\AgencyBundle\Entity\AgencyObject", mappedBy="custom")
     * @var Object[]
     */
    protected $objects;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Orders", mappedBy="custom")
     * @var Orders[]
     */
    protected $orders;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="custom")
     * @var Invoice[]
     */
    protected $invoices;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\StoreBundle\Entity\Auction", mappedBy="buyer")
     * @var BuyAuctions[]
     */
    protected $buyAuctions;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\StoreBundle\Entity\Auction", mappedBy="seller")
     * @var SellAuctions[]
     */
    protected $sellAuctions;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\StoreBundle\Entity\Store", inversedBy="customs")
     * @var Store
     */
    protected $store;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "您的姓名至少要超過 {{ limit }} 個字元",
     *      maxMessage = "您的名字不可以超過{{ limit }} 個字元"
     * )
     * @ORM\Column(type="string", length=100)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $sex;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $county;

    /**
     * @ORM\Column(type="string", length=15)
     */
    protected $district;

    /**
     * @Assert\Length(
     *      min = 5,
     *      max = 200,
     *      minMessage = "地址至少要超過五個字元",
     *      maxMessage = "地址不可超過200個字元"
     * )
     * @ORM\Column(type="string", length=255)
     */
    protected $address;

    /**
     * @ORM\Column(type="datetime", length=50, nullable=true, length=30)
     */
    protected $createtime;

     /**
     * @ORM\Column(type="datetime", length=50, nullable=true)
     */
    protected $birthday;

    /**
     * @Assert\Regex(
     *     pattern = "/^09[0-9]{8}$/",
     *     message = "手機格式必需為 09 開頭的10碼數字，如 0912345678"
     * )
     * @ORM\Column(type="string", length=20)
     */
    protected $mobil;

    /**
     * @Assert\Email(
     *     message = "'{{ value }}' 不是一個合法的信箱",
     *     checkMX = true
     * )
     * @ORM\Column(type="string", length=30)
     */
    protected $email;

    /**
     * @Assert\Email(
     *     message = "'{{ value }}' 不是一個合法的信箱",
     *     checkMX = true
     * )
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $bkemail;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $memo;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $isProhibit;

    /**
     * @Assert\Regex(
     *     pattern = "/^(?=^.{6,25}$)((?=.*[0-9])(?=.*[a-z|A-Z]))^.*$/",
     *     message = "密碼必需包含至少各一位英文字母、數字, 長度為 6 ~ 25 個字元"
     * )
     * @ORM\Column(type="string", length=30)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $crypPassword;

    /**
     * @ORM\Column(type="integer")
     */
    protected $dividend;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $csrf;

    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $preCsrf;

    /**
     * 願望清單，會把 商品id 陣列序列化成 json 字串儲存
     *
     * @example {1, 24, 155, 1889}
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $whishlist;

    /**
     * 激活帳號的 key
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $activeKey;

    /**
     * 帳號是否激活(僅用來判斷網站客戶，實體門市客戶不受限)
     * 如果帳號未激活，將無法進行結帳動作
     *
     * @ORM\Column(type="boolean")
     */
    protected $isActive;

    /**
     * fb 登入token
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $fbToken;

    /**
     * google 登入token
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $googleToken;

    /**
     * @ORM\Column(type="string", length=155, nullable=true)
     */
    protected $facebookAccount;

    /**
     * @ORM\Column(type="string", length=155, nullable=true)
     */
    protected $lineAccount;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->goodsPassports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->objects = new \Doctrine\Common\Collections\ArrayCollection();
        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->invoices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->isProhibit = false;
        $this->createtime = new \DateTime();
        $this->csrf = uniqid();
        $this->isActive = false;
        $this->crypPassword = md5($this->csrf . rand(0, 100000));
        $this->activeKey = $this->genActiveKey();
        $this->county = '未填寫';
        $this->district = '未填寫';
        $this->dividend = 0;
        $this->preCsrf = md5($this->csrf);
    }

    public function getExcelFormatData()
    {
        return $this->getName() . ' ' . $this->getMobil();
    }

    public function genActiveKey()
    {
        return substr(sha1(time() . self::SALT) . md5($this->csrf), 0, 255);
    }

    /**
     * 根據所屬店家判斷是否為官網會員
     *
     * @return boolean
     */
    public function isWeb()
    {
        return ($this->store->getSn() === '#');
    }

    public function handleFbResponse($userNode)
    {
        switch($userNode['gender'])
        {
            case 'male':
                $this->sex = '先生';

                break;

            case 'female':
                $this->sex = '小姐';

                break;

            default:
                $this->sex = '保密';

                break;
        }

        $this->email = $userNode['email'];
        $this->name = $userNode['name'];
        $this->fbToken = $userNode['id'];
        $this->isActive = true;
        $this->address = '';
        $this->mobil = '';

        return $this;
    }

    public function handleGoogleResponse(array $node)
    {
        $this->sex = '保密';

        $this->email = $node['email'];
        $this->name = $node['name'];
        $this->googleToken = $node['sub'];
        $this->isActive = true;
        $this->address = '';
        $this->mobil = '';

        return $this;
    }

    /**
     * 取得密碼加鹽後字串
     *
     * @param  string $password
     * @return string
     */
    private function crypt($password)
    {
        return md5(substr($password, 0, 1) .  substr($password, 0, 3) . $password . self::SALT);
    }

    /**
     * 密碼規格為 email 前兩個字元 + 密碼 + 特殊常數
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function autoSetCrypPassword()
    {
        if ($this->password === self::DEFAULT_PASSWORD) {
            return $this;
        }

        $this->setCrypPassword($this->crypt($this->password));

        //暗碼設置完成後，將原本的密碼用預設密碼覆蓋掉
        $this->setPassword(self::DEFAULT_PASSWORD);

        return $this;
    }

    /**
     * 驗證登入，特別注意密碼會加鹽 id
     *
     * @param  string $password
     * @param  string $email
     * @return boolean
     */
    public function verifyLogin($password, $email)
    {
        return (($this->crypPassword == $this->crypt($password)) && ($this->email == $email));
    }

    /**
     * Is the custom belong to the given user?
     *
     * @param  \Woojin\UserBundle\Entity\User   $user
     * @return boolean
     */
    public function isBelongUserStore(User $user)
    {
        return $this->getStore()->getId() === $user->getStore()->getId();
    }

    /**
     * Check if this custom has no orders
     *
     * @return boolean
     */
    public function hasNoOrders()
    {
        return 0 === $this->getOrders()->count();
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
     * Set name
     *
     * @param string $name
     * @return Custom
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set sex
     *
     * @param string $sex
     * @return Custom
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return string
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Custom
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
     * Set createtime
     *
     * @param \DateTime $createtime
     * @return Custom
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
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return Custom
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set mobil
     *
     * @param string $mobil
     * @return Custom
     */
    public function setMobil($mobil)
    {
        $this->mobil = $mobil;

        return $this;
    }

    /**
     * Get mobil
     *
     * @return string
     */
    public function getMobil()
    {
        return $this->mobil;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Custom
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set memo
     *
     * @param string $memo
     * @return Custom
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
     * Add goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     * @return Custom
     */
    public function addGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports)
    {
        $this->goodsPassports[] = $goodsPassports;

        return $this;
    }

    /**
     * Remove goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     */
    public function removeGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports)
    {
        $this->goodsPassports->removeElement($goodsPassports);
    }

    /**
     * Get goodsPassports
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGoodsPassports()
    {
        return $this->goodsPassports;
    }

    /**
     * Add objects
     *
     * @param \Woojin\AgencyBundle\Entity\AgencyObject $objects
     * @return Custom
     */
    public function addObject(AgencyObject $objects)
    {
        $this->objects[] = $objects;

        return $this;
    }

    /**
     * Remove objects
     *
     * @param \Woojin\AgencyBundle\Entity\AgencyObject $objects
     */
    public function removeObject(AgencyObject $objects)
    {
        $this->objects->removeElement($objects);
    }

    /**
     * Get objects
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     * Add orders
     *
     * @param \Woojin\OrderBundle\Entity\Orders $orders
     * @return Custom
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
     * Add invoices
     *
     * @param \Woojin\OrderBundle\Entity\Invoice $invoices
     * @return Custom
     */
    public function addInvoice(\Woojin\OrderBundle\Entity\Invoice $invoices)
    {
        $this->invoices[] = $invoices;

        return $this;
    }

    /**
     * Remove invoices
     *
     * @param \Woojin\OrderBundle\Entity\Invoice $invoices
     */
    public function removeInvoice(\Woojin\OrderBundle\Entity\Invoice $invoices)
    {
        $this->invoices->removeElement($invoices);
    }

    /**
     * Get invoices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInvoices()
    {
        return $this->invoices;
    }

    /**
     * Set store
     *
     * @param \Woojin\StoreBundle\Entity\Store $store
     * @return Custom
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
     * Set password
     *
     * @param string $password
     * @return Custom
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set csrf
     *
     * @param string $csrf
     * @return Custom
     */
    public function setCsrf($csrf)
    {
        $this->csrf = md5($csrf . $this->id . time() . rand(0, 1000) . $csrf);

        return $this;
    }

    /**
     * Get csrf
     *
     * @return string
     */
    public function getCsrf()
    {
        return $this->csrf;
    }

    /**
     * Set isProhibit
     *
     * @param boolean $isProhibit
     * @return Custom
     */
    public function setIsProhibit($isProhibit)
    {
        $this->isProhibit = $isProhibit;

        return $this;
    }

    /**
     * Get isProhibit
     *
     * @return boolean
     */
    public function getIsProhibit()
    {
        return $this->isProhibit;
    }

    /**
     * Set crypPassword
     *
     * @param string $crypPassword
     * @return Custom
     */
    public function setCrypPassword($crypPassword)
    {
        $this->crypPassword = $crypPassword;

        return $this;
    }

    /**
     * Get crypPassword
     *
     * @return string
     */
    public function getCrypPassword()
    {
        return $this->crypPassword;
    }

    /**
     * Set whishlist
     *
     * @param string $whishlist
     * @return Custom
     */
    public function setWhishlist($whishlist)
    {
        $this->whishlist = $whishlist;

        return $this;
    }

    /**
     * Get whishlist
     *
     * @return string
     */
    public function getWhishlist()
    {
        return $this->whishlist;
    }

    /**
     * Set activeKey
     *
     * @param string $activeKey
     * @return Custom
     */
    public function setActiveKey($activeKey)
    {
        $this->activeKey = $activeKey;

        return $this;
    }

    /**
     * Get activeKey
     *
     * @return string
     */
    public function getActiveKey()
    {
        return $this->activeKey;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Custom
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set preCsrf
     *
     * @param string $preCsrf
     * @return Custom
     */
    public function setPreCsrf($preCsrf)
    {
        $this->preCsrf = $preCsrf;

        return $this;
    }

    /**
     * Get preCsrf
     *
     * @return string
     */
    public function getPreCsrf()
    {
        return $this->preCsrf;
    }

    /**
     * Set county
     *
     * @param string $county
     * @return Custom
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
     * @return Custom
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
     * Set dividend
     *
     * @param integer $dividend
     * @return Custom
     */
    public function setDividend($dividend)
    {
        $this->dividend = $dividend;

        return $this;
    }

    /**
     * Get dividend
     *
     * @return integer
     */
    public function getDividend()
    {
        return $this->dividend;
    }

    /**
     * Add customOpes
     *
     * @param \Woojin\OrderBundle\Entity\CustomOpe $customOpes
     * @return Custom
     */
    public function addCustomOpe(\Woojin\OrderBundle\Entity\CustomOpe $customOpes)
    {
        $this->customOpes[] = $customOpes;

        return $this;
    }

    /**
     * Remove customOpes
     *
     * @param \Woojin\OrderBundle\Entity\CustomOpe $customOpes
     */
    public function removeCustomOpe(\Woojin\OrderBundle\Entity\CustomOpe $customOpes)
    {
        $this->customOpes->removeElement($customOpes);
    }

    /**
     * Get customOpes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustomOpes()
    {
        return $this->customOpes;
    }

    /**
     * Add behalfs
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $behalfs
     * @return Custom
     */
    public function addBehalf(\Woojin\GoodsBundle\Entity\Behalf $behalfs)
    {
        $this->behalfs[] = $behalfs;

        return $this;
    }

    /**
     * Remove behalfs
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $behalfs
     */
    public function removeBehalf(\Woojin\GoodsBundle\Entity\Behalf $behalfs)
    {
        $this->behalfs->removeElement($behalfs);
    }

    /**
     * Get behalfs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBehalfs()
    {
        return $this->behalfs;
    }

    /**
     * Add benefits
     *
     * @param \Woojin\OrderBundle\Entity\Benefit $benefits
     * @return Custom
     */
    public function addBenefit(\Woojin\OrderBundle\Entity\Benefit $benefits)
    {
        $this->benefits[] = $benefits;

        return $this;
    }

    /**
     * Remove benefits
     *
     * @param \Woojin\OrderBundle\Entity\Benefit $benefits
     */
    public function removeBenefit(\Woojin\OrderBundle\Entity\Benefit $benefits)
    {
        $this->benefits->removeElement($benefits);
    }

    /**
     * Get benefits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBenefits()
    {
        return $this->benefits;
    }

    /**
     * Set bkemail
     *
     * @param string $bkemail
     * @return Custom
     */
    public function setBkemail($bkemail)
    {
        $this->bkemail = $bkemail;

        return $this;
    }

    /**
     * Get bkemail
     *
     * @return string
     */
    public function getBkemail()
    {
        return $this->bkemail;
    }

    /**
     * Set fbToken
     *
     * @param string $fbToken
     * @return Custom
     */
    public function setFbToken($fbToken)
    {
        $this->fbToken = $fbToken;

        return $this;
    }

    /**
     * Get fbToken
     *
     * @return string
     */
    public function getFbToken()
    {
        return $this->fbToken;
    }

    /**
     * Set googleToken
     *
     * @param string $googleToken
     * @return Custom
     */
    public function setGoogleToken($googleToken)
    {
        $this->googleToken = $googleToken;

        return $this;
    }

    /**
     * Get googleToken
     *
     * @return string
     */
    public function getGoogleToken()
    {
        return $this->googleToken;
    }

    /**
     * Add buyAuction
     *
     * @param \Woojin\StoreBundle\Entity\Auction $buyAuction
     *
     * @return Custom
     */
    public function addBuyAuction(\Woojin\StoreBundle\Entity\Auction $buyAuction)
    {
        $this->buyAuctions[] = $buyAuction;

        return $this;
    }

    /**
     * Remove buyAuction
     *
     * @param \Woojin\StoreBundle\Entity\Auction $buyAuction
     */
    public function removeBuyAuction(\Woojin\StoreBundle\Entity\Auction $buyAuction)
    {
        $this->buyAuctions->removeElement($buyAuction);
    }

    /**
     * Get buyAuctions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBuyAuctions()
    {
        return $this->buyAuctions;
    }

    /**
     * Add sellAuction
     *
     * @param \Woojin\StoreBundle\Entity\Auction $sellAuction
     *
     * @return Custom
     */
    public function addSellAuction(\Woojin\StoreBundle\Entity\Auction $sellAuction)
    {
        $this->sellAuctions[] = $sellAuction;

        return $this;
    }

    /**
     * Remove sellAuction
     *
     * @param \Woojin\StoreBundle\Entity\Auction $sellAuction
     */
    public function removeSellAuction(\Woojin\StoreBundle\Entity\Auction $sellAuction)
    {
        $this->sellAuctions->removeElement($sellAuction);
    }

    /**
     * Get sellAuctions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSellAuctions()
    {
        return $this->sellAuctions;
    }

    /**
     * Set facebookAccount
     *
     * @param string $facebookAccount
     *
     * @return Custom
     */
    public function setFacebookAccount($facebookAccount)
    {
        $this->facebookAccount = $facebookAccount;

        return $this;
    }

    /**
     * Get facebookAccount
     *
     * @return string
     */
    public function getFacebookAccount()
    {
        return $this->facebookAccount;
    }

    /**
     * Set lineAccount
     *
     * @param string $lineAccount
     *
     * @return Custom
     */
    public function setLineAccount($lineAccount)
    {
        $this->lineAccount = $lineAccount;

        return $this;
    }

    /**
     * Get lineAccount
     *
     * @return string
     */
    public function getLineAccount()
    {
        return $this->lineAccount;
    }
}

<?php

namespace Woojin\UserBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Woojin\Utility\Avenue\Avenue;

/**
 * @ORM\Table(name="role")
 * @ORM\Entity()
 */
class Role implements RoleInterface
{
    const BRAND                         = 0;
    const PATTERN                       = 1;
    const MT                            = 2;
    const LEVEL                         = 3;
    const SOURCE                        = 4;
    const COLOR                         = 5;
    const PAYTYPE                       = 6;
    const EDIT_WEBPRICE_OWN             = 7;
    const EDIT_WEBPRICE_ALL             = 8;
    const EDIT_PRICE_OWN                = 9;
    const EDIT_PRICE_ALL                = 10;
    const READ_COST_OWN                 = 11;
    const READ_COST_ALL                 = 12;
    const READ_PRODUCT_OWN              = 13;
    const READ_PRODUCT_ALL              = 14;
    const READ_ORDER_OWN                = 15;
    const READ_ORDER_ALL                = 16;
    const CANCEL_ORDER                  = 17;
    const EDIT_OPE_DATETIME             = 18;
    const CONSIGN_TO_PURCHASE           = 19;
    const READ_CUSTOM                   = 20;
    const EDIT_CUSTOM                   = 21;
    const MOVE_REQUEST                  = 22;
    const MOVE_RESPONSE                 = 23;
    const PURCHASE                      = 24;
    const SELL                          = 25;
    const MULTI_SELL                    = 26;
    const ACTIVITY_SELL                 = 27;
    const ACTIVITY_MANAGE               = 28;
    const WEB_ORDER_MANAGE              = 29;
    const BEHALF_MANAGE                 = 30;
    const HAND_GEN_INVOICE              = 31;
    const BATCH_UPLOAD                  = 32;
    const CHECK_STOCK                   = 33;
    const MOVE_RELATE                   = 34;
    const CONSIGN_INFORM                = 35;
    const STORE_SETTING                 = 36;
    const PROMOTION_MANAGE              = 37;
    const CUSTOM_SN_IMPORT              = 38;
    const TIMELINESS_SETTINGS           = 39;
    const BENEFIT_REPORT_OWN            = 40; // 毛利報表本店
    const BENEFIT_REPORT_ALL            = 41; // 毛利報表全
    const STOCK_REPORT_OWN              = 42;
    const STOCK_REPORT_ALL              = 43;
    const MOVE_REPORT_OWN               = 44;
    const MOVE_REPORT_ALL               = 45;
    const USER_SELF_MANAGE              = 46;
    const USER_OWN_MANAGE               = 47;
    const USER_ALL_MANAGE               = 48;
    const ROLE_MANAGE                   = 49;
    const EDIT_COST_OWN                 = 50;
    const EDIT_COST_ALL                 = 51;
    const EDIT_PRODUCT_OWN              = 52;
    const EDIT_PRODUCT_ALL              = 53;
    const BENEFIT_MANAGE                = 54; // 購物金管理
    const EDIT_SEO_SLOGAN               = 55;
    const EDIT_OTHER_HOLIDAY            = 56;
    const CANCEL_IN_TYPE_ORDER          = 57;
    const EDIT_ACTIVITY_DISPLAY         = 58;

    protected $map = array(
        'BRAND'                         => '編輯品牌',
        'PATTERN'                       => '編輯款式',
        'MT'                            => '編輯材質',
        'LEVEL'                         => '編輯新舊',
        'SOURCE'                        => '編輯來源',
        'COLOR'                         => '編輯顏色',
        'PAYTYPE'                       => '編輯付費方式',

        'EDIT_WEBPRICE_OWN'             => '編輯本店商品網路售價',
        'EDIT_WEBPRICE_ALL'             => '編輯所有商品網路售價',
        'EDIT_PRICE_OWN'                => '編輯本店商品價格',
        'EDIT_PRICE_ALL'                => '編輯所有店商品價格',
        'EDIT_PRODUCT_OWN'              => '編輯本店商品',
        'EDIT_PRODUCT_ALL'              => '編輯所有商品',

        'READ_COST_OWN'                 => '查看本店商品成本',
        'READ_COST_ALL'                 => '查看所有商品成本',
        'EDIT_COST_OWN'                 => '編輯本店商品成本',
        'EDIT_COST_ALL'                 => '編輯所有商品成本',

        'READ_PRODUCT_OWN'              => '查看本店商品',
        'READ_PRODUCT_ALL'              => '查看所有商品',
        'READ_ORDER_OWN'                => '查看本店訂單',
        'READ_ORDER_ALL'                => '查看所有訂單',

        'CANCEL_ORDER'                  => '取消訂單',
        'CANCEL_IN_TYPE_ORDER'          => '下架商品',

        'EDIT_OPE_DATETIME'             => '編輯操作時間',

        'CONSIGN_TO_PURCHASE'           => '寄賣轉訂單',

        'READ_CUSTOM'                   => '查看客戶資料',
        'EDIT_CUSTOM'                   => '編輯客戶資料',

        'MOVE_REQUEST'                  => '發起調貨請求',
        'MOVE_RESPONSE'                 => '接收調貨請求',

        'PURCHASE'                      => '進貨',

        'SELL'                          => '銷貨',
        'MULTI_SELL'                    => '多筆銷貨',

        'ACTIVITY_SELL'                 => '活動銷貨',
        'ACTIVITY_MANAGE'               => '活動管理',
        'EDIT_ACTIVITY_DISPLAY'         => '編輯活動顯示',

        'WEB_ORDER_MANAGE'              => '官網訂單管理',
        'BEHALF_MANAGE'                 => '代購管理',
        'HAND_GEN_INVOICE'              => '手KEY建立銷貨憑證',
        'BATCH_UPLOAD'                  => '批次上傳',
        'CHECK_STOCK'                   => '店內盤點',
        'MOVE_RELATE'                   => '調貨相關',
        'CONSIGN_INFORM'                => '寄賣通知',
        'STORE_SETTING'                 => '門市設定',
        'PROMOTION_MANAGE'              => '促銷活動管理',
        'CUSTOM_SN_IMPORT'              => '自定義內碼匯入',
        'TIMELINESS_SETTINGS'           => '搶購活動設定',

        'BENEFIT_REPORT_OWN'            => '本店毛利報表',
        'BENEFIT_REPORT_ALL'            => '所有毛利報表',

        'STOCK_REPORT_OWN'              => '本店庫存報表',
        'STOCK_REPORT_ALL'              => '所有庫存報表',

        'MOVE_REPORT_OWN'               => '本店調貨報表',
        'MOVE_REPORT_ALL'               => '所有調貨報表',

        'USER_SELF_MANAGE'              => '個人帳號管理',
        'USER_OWN_MANAGE'               => '本店帳號管理',
        'USER_ALL_MANAGE'               => '所有帳號管理',

        'EDIT_OTHER_HOLIDAY'            => '修改門市成員假期',

        'ROLE_MANAGE'                   => '權限管理',

        'BENEFIT_MANAGE'                => '購物金管理',

        'EDIT_SEO_SLOGAN'               => '關鍵字管理'
    );

    public static function getRolelist()
    {
        $oClass = new \ReflectionClass(__CLASS__);

        return $oClass->getConstants();
    }

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="roles")
     * @var User[]
     */
    private $user;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=30)
     */
    private $name;

    /**
     * @ORM\Column(name="role", type="string", length=20, unique=true)
     */
    private $role;

    /**
     * @ORM\Column(name="chmod", type="string", length=100)
     */
    private $chmod;


    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public static function getMap()
    {
        return $this->map;
    }

    /**
     * 判斷是否擁有權限
     *
     * @param  string
     * @return boolean
     */
    public function hasAuth($str)
    {
        return ((int) substr($this->chmod, constant('self::' . strtoupper($str)), 1) === 1);
    }

    /**
     * 開發者自行新增之方法
     * Set role
     *
     * @param string $role
     * @return Role
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * 開發者自行新增之方法
     *
     * @return \Woojin\UserBundle\Entity\User $user
     */
    public function getUsers()
    {
        $iterator = $this->user->getIterator();

        while ($iterator->valid()) {
            if (!$iterator->current()->getIsActive() || $iterator->current()->getId() === Avenue::WEB_CHIEF) {
                $this->user->removeElement($iterator->current());
            }

            $iterator->next();
        }

        return $this->user;
    }

    /**
     * @see RoleInterface
     */
    public function getRole()
    {
        return $this->role;
    }

    // ... getters and setters for each property

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
     * @return Role
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
     * Add user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return Role
     */
    public function addUser(\Woojin\UserBundle\Entity\User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     */
    public function removeUser(\Woojin\UserBundle\Entity\User $user)
    {
        $this->user->removeElement($user);
    }

    /**
     * Get user
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set chmod
     *
     * @param string $chmod
     * @return Role
     */
    public function setChmod($chmod)
    {
        $this->chmod = $chmod;

        return $this;
    }

    /**
     * Get chmod
     *
     * @return string
     */
    public function getChmod()
    {
        return $this->chmod;
    }

    public function __toString()
    {
        return $this->name;
    }
}

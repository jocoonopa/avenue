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
    // LAST: 57
    const ACTIVITY_SELL                 = 27;
    const ACTIVITY_MANAGE               = 28;

    const BATCH_UPLOAD                  = 32;
    const BEHALF_MANAGE                 = 30;
    const BENEFIT_REPORT_OWN            = 40; // 毛利報表本店
    const BENEFIT_REPORT_ALL            = 41; // 毛利報表全
    const BENEFIT_MANAGE                = 54; // 購物金管理
    const BRAND                         = 0;

    const CANCEL_ORDER                  = 17;
    const CANCEL_IN_TYPE_ORDER          = 57;
    const CHECK_STOCK                   = 33;
    const COLOR                         = 5;
    const CONSIGN_INFORM                = 35;
    const CONSIGN_TO_PURCHASE           = 19;
    const CUSTOM_SN_IMPORT              = 38;

    const EDIT_WEBPRICE_OWN             = 7;
    const EDIT_WEBPRICE_ALL             = 8;
    const EDIT_PRICE_OWN                = 9;
    const EDIT_PRICE_ALL                = 10;
    const EDIT_PRODUCT_OWN              = 52;
    const EDIT_PRODUCT_ALL              = 53;
    const EDIT_COST_OWN                 = 50;
    const EDIT_COST_ALL                 = 51;
    const EDIT_CUSTOM                   = 21;
    const EDIT_OPE_DATETIME             = 18;
    const EDIT_SEO_SLOGAN               = 55;
    const EDIT_OTHER_HOLIDAY            = 56;

    const HAND_GEN_INVOICE              = 31;

    const LEVEL                         = 3;

    const MOVE_RELATE                   = 34;
    const MOVE_REPORT_OWN               = 44;
    const MOVE_REPORT_ALL               = 45;
    const MOVE_REQUEST                  = 22;
    const MOVE_RESPONSE                 = 23;
    const MT                            = 2;
    const MULTI_SELL                    = 26;

    const PATTERN                       = 1;
    const PAYTYPE                       = 6;
    const PURCHASE                      = 24;
    const PROMOTION_MANAGE              = 37;

    const READ_COST_OWN                 = 11;
    const READ_COST_ALL                 = 12;
    const READ_PRODUCT_OWN              = 13;
    const READ_PRODUCT_ALL              = 14;
    const READ_ORDER_OWN                = 15;
    const READ_ORDER_ALL                = 16;
    const READ_CUSTOM                   = 20;
    const ROLE_MANAGE                   = 49;

    const SELL                          = 25;
    const SOURCE                        = 4;
    const STOCK_REPORT_OWN              = 42;
    const STOCK_REPORT_ALL              = 43;
    const STORE_SETTING                 = 36;

    const TIMELINESS_SETTINGS           = 39;

    const USER_SELF_MANAGE              = 46;
    const USER_OWN_MANAGE               = 47;
    const USER_ALL_MANAGE               = 48;
    
    const WEB_ORDER_MANAGE              = 29;

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
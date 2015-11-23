<?php
// src/Acme/UserBundle/Entity/User.php
namespace Woojin\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * Woojin\UserBundle\Entity\User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Woojin\UserBundle\Entity\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\StoreBundle\Entity\Holiday", mappedBy="user")
     * @var holidays[]
     */
    protected $holidays;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="AvenueClue", mappedBy="user")
     * @var clues[]
     */
    protected $clues;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\Behalf", mappedBy="user")
     * @var Behalfs[]
     */
    protected $behalfs;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\Promotion", mappedBy="user")
     * @var Promotions[]
     */
    protected $promotions;

    /**
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="user")
     * @var Role
     */
    private $roles;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\StoreBundle\Entity\MetaRecord", mappedBy="user")
     * @var MetaRecords[]
     */
    protected $meta_records;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\OrderBundle\Entity\Ope", mappedBy="user")
     * @var Ope[]
     */
    protected $opes;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\Move", mappedBy="catcher")
     * @var CatchMoves[]
     */
    protected $catchMoves;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\Move", mappedBy="thrower")
     * @var ThrowMoves[]
     */
    protected $throwMoves;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\Move", mappedBy="creater")
     * @var CreateMoves[]
     */
    protected $createMoves;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\GoodsBundle\Entity\Move", mappedBy="closer")
     * @var CloseMoves[]
     */
    protected $closeMoves;


    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\AgencyBundle\Entity\OperationRecord", mappedBy="user")
     * @var OperationRecord[]
     */
    protected $operation_records;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="UsersLog", mappedBy="user")
     * @var UsersLog[]
     */
    protected $users_logs;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="UsersHabit", mappedBy="user")
     * @var UsersHabit[]
     */
    protected $users_habits;

    /**
     * @ORM\ManyToOne(targetEntity="\Woojin\StoreBundle\Entity\Store", inversedBy="users")
     * @var Store
     */
    protected $store;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="\Woojin\StoreBundle\Entity\StockTakeRecord", mappedBy="user")
     * @var StockTakeRecord[]
     */
    protected $stock_take_record;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $salt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=60, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=60)
     */
    private $mobil;

    /**
     * @ORM\Column(type="datetime", length=60)
     */
    private $createtime;

    /**
     * @ORM\Column(type="datetime", length=60, nullable=true)
     */
    private $stoptime;

    /**
     * @ORM\Column(type="integer", length=60)
     */
    private $chmod;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    public function __construct()
    {
        $this->isActive = true;
        $this->salt = md5(uniqid(null, true));
    }

    /**
     * 開發者自行新增之方法
     * 
     * Get roles
     *
     * @return \Woojin\UserBundle\Entity\Role
     */
    public function getRole()
    {
        return $this->roles;
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
    return $this->username;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
    return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
    return $this->password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
    return array('ROLE_USER');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
    return serialize(array(
        $this->id,
    ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
    list (
        $this->id,
    ) = unserialize($serialized);
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
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
    $this->username = $username;
    
    return $this;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
    $this->salt = $salt;
    
    return $this;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
    $this->password = $password;
    
    return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
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
     * Set mobil
     *
     * @param string $mobil
     * @return User
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
     * Set createtime
     *
     * @param \DateTime $createtime
     * @return User
     */
    public function setCreatetime($createtime)
    {
    $this->createtime = new \DateTime($createtime);
    
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
     * Set stoptime
     *
     * @param \DateTime $stoptime
     * @return User
     */
    public function setStoptime($stoptime)
    {
    $this->stoptime = new \DateTime($stoptime);
    
    return $this;
    }

    /**
     * Get stoptime
     *
     * @return \DateTime 
     */
    public function getStoptime()
    {
    return $this->stoptime;
    }

    /**
     * Set store_id
     *
     * @param integer $storeId
     * @return User
     */
    public function setStoreId($storeId)
    {
    $this->store_id = $storeId;
    
    return $this;
    }

    /**
     * Get store_id
     *
     * @return integer 
     */
    public function getStoreId()
    {
    return $this->store_id;
    }

    /**
     * Set chmod
     *
     * @param integer $chmod
     * @return User
     */
    public function setChmod($chmod)
    {
    $this->chmod = $chmod;
    
    return $this;
    }

    /**
     * Get chmod
     *
     * @return integer 
     */
    public function getChmod()
    {
    return $this->chmod;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return User
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
     * Set roles
     *
     * @param \Woojin\UserBundle\Entity\Role $roles
     * @return User
     */
    public function setRoles(\Woojin\UserBundle\Entity\Role $roles = null)
    {
        $this->roles = $roles;
        
        return $this;
    }

    /**
     * Set roles
     *
     * @param \Woojin\UserBundle\Entity\Role $roles
     * @return User
     */
    public function setRole(\Woojin\UserBundle\Entity\Role $roles = null)
    {
        $this->roles = $roles;
        
        return $this;
    }

    /**
     * Add meta_record
     *
     * @param \Woojin\StoreBundle\Entity\MetaRecord $metaRecord
     * @return User
     */
    public function addMetaRecord(\Woojin\StoreBundle\Entity\MetaRecord $metaRecord)
    {
    $this->meta_record[] = $metaRecord;
    
    return $this;
    }

    /**
     * Remove meta_record
     *
     * @param \Woojin\StoreBundle\Entity\MetaRecord $metaRecord
     */
    public function removeMetaRecord(\Woojin\StoreBundle\Entity\MetaRecord $metaRecord)
    {
    $this->meta_record->removeElement($metaRecord);
    }

    /**
     * Get meta_record
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMetaRecord()
    {
    return $this->meta_record;
    }

    /**
     * Add ope
     *
     * @param \Woojin\OrderBundle\Entity\Ope $ope
     * @return User
     */
    public function addOpe(\Woojin\OrderBundle\Entity\Ope $ope)
    {
    $this->ope[] = $ope;
    
    return $this;
    }

    /**
     * Remove ope
     *
     * @param \Woojin\OrderBundle\Entity\Ope $ope
     */
    public function removeOpe(\Woojin\OrderBundle\Entity\Ope $ope)
    {
    $this->ope->removeElement($ope);
    }

    /**
     * Get ope
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOpe()
    {
    return $this->ope;
    }

    /**
     * Add users_log
     *
     * @param \Woojin\UserBundle\Entity\UsersLog $usersLog
     * @return User
     */
    public function addUsersLog(\Woojin\UserBundle\Entity\UsersLog $usersLog)
    {
    $this->users_log[] = $usersLog;
    
    return $this;
    }

    /**
     * Remove users_log
     *
     * @param \Woojin\UserBundle\Entity\UsersLog $usersLog
     */
    public function removeUsersLog(\Woojin\UserBundle\Entity\UsersLog $usersLog)
    {
    $this->users_log->removeElement($usersLog);
    }

    /**
     * Get users_log
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersLog()
    {
    return $this->users_log;
    }

    /**
     * Add users_habit
     *
     * @param \Woojin\UserBundle\Entity\UsersHabit $usersHabit
     * @return User
     */
    public function addUsersHabit(\Woojin\UserBundle\Entity\UsersHabit $usersHabit)
    {
    $this->users_habit[] = $usersHabit;
    
    return $this;
    }

    /**
     * Remove users_habit
     *
     * @param \Woojin\UserBundle\Entity\UsersHabit $usersHabit
     */
    public function removeUsersHabit(\Woojin\UserBundle\Entity\UsersHabit $usersHabit)
    {
    $this->users_habit->removeElement($usersHabit);
    }

    /**
     * Get users_habit
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersHabit()
    {
    return $this->users_habit;
    }

    /**
     * Set store
     *
     * @param \Woojin\StoreBundle\Entity\Store $store
     * @return User
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
     * Get roles
     *
     * @return \Woojin\UserBundle\Entity\Role
     */
    public function getTheRoles()
    {
        return $this->roles;
    }

    public function isAccountNonExpired()
    {
    return true;
    }

    public function isAccountNonLocked()
    {
    return true;
    }

    public function isCredentialsNonExpired()
    {
    return true;
    }

    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * Set roles_id
     *
     * @param integer $rolesId
     * @return User
     */
    public function setRolesId($rolesId)
    {
        $this->roles_id = $rolesId;
    
        return $this;
    }

    /**
     * Get roles_id
     *
     * @return integer 
     */
    public function getRolesId()
    {
        return $this->roles_id;
    }

    /**
     * Add operation_record
     *
     * @param \Woojin\AgencyBundle\Entity\OperationRecord $operationRecord
     * @return User
     */
    public function addOperationRecord(\Woojin\AgencyBundle\Entity\OperationRecord $operationRecord)
    {
        $this->operation_records[] = $operationRecord;
    
        return $this;
    }

    /**
     * Remove operation_record
     *
     * @param \Woojin\AgencyBundle\Entity\OperationRecord $operationRecord
     */
    public function removeOperationRecord(\Woojin\AgencyBundle\Entity\OperationRecord $operationRecord)
    {
        $this->operation_records->removeElement($operationRecord);
    }

    /**
     * Get operation_record
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOperationRecords()
    {
        return $this->operation_records;
    }

    /**
     * Add stock_take_record
     *
     * @param \Woojin\StoreBundle\Entity\StockTakeRecord $stockTakeRecord
     * @return User
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

    /**
     * Add catchMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $catchMoves
     * @return User
     */
    public function addCatchMove(\Woojin\GoodsBundle\Entity\Move $catchMoves)
    {
        $this->catchMoves[] = $catchMoves;
    
        return $this;
    }

    /**
     * Remove catchMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $catchMoves
     */
    public function removeCatchMove(\Woojin\GoodsBundle\Entity\Move $catchMoves)
    {
        $this->catchMoves->removeElement($catchMoves);
    }

    /**
     * Get catchMoves
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCatchMoves()
    {
        return $this->catchMoves;
    }

    /**
     * Add throwMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $throwMoves
     * @return User
     */
    public function addThrowMove(\Woojin\GoodsBundle\Entity\Move $throwMoves)
    {
        $this->throwMoves[] = $throwMoves;
    
        return $this;
    }

    /**
     * Remove throwMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $throwMoves
     */
    public function removeThrowMove(\Woojin\GoodsBundle\Entity\Move $throwMoves)
    {
        $this->throwMoves->removeElement($throwMoves);
    }

    /**
     * Get throwMoves
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getThrowMoves()
    {
        return $this->throwMoves;
    }

    /**
     * Add createMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $createMoves
     * @return User
     */
    public function addCreateMove(\Woojin\GoodsBundle\Entity\Move $createMoves)
    {
        $this->createMoves[] = $createMoves;
    
        return $this;
    }

    /**
     * Remove createMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $createMoves
     */
    public function removeCreateMove(\Woojin\GoodsBundle\Entity\Move $createMoves)
    {
        $this->createMoves->removeElement($createMoves);
    }

    /**
     * Get createMoves
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCreateMoves()
    {
        return $this->createMoves;
    }

    /**
     * Add closeMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $closeMoves
     * @return User
     */
    public function addCloseMove(\Woojin\GoodsBundle\Entity\Move $closeMoves)
    {
        $this->closeMoves[] = $closeMoves;
    
        return $this;
    }

    /**
     * Remove closeMoves
     *
     * @param \Woojin\GoodsBundle\Entity\Move $closeMoves
     */
    public function removeCloseMove(\Woojin\GoodsBundle\Entity\Move $closeMoves)
    {
        $this->closeMoves->removeElement($closeMoves);
    }

    /**
     * Get closeMoves
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCloseMoves()
    {
        return $this->closeMoves;
    }

    public function __toString()
    {
        return $this->username;
    }

    /**
     * Get meta_records
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMetaRecords()
    {
        return $this->meta_records;
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
     * Get users_logs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersLogs()
    {
        return $this->users_logs;
    }

    /**
     * Get users_habits
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsersHabits()
    {
        return $this->users_habits;
    }

    /**
     * Add promotions
     *
     * @param \Woojin\GoodsBundle\Entity\Promotion $promotions
     * @return User
     */
    public function addPromotion(\Woojin\GoodsBundle\Entity\Promotion $promotions)
    {
        $this->promotions[] = $promotions;
    
        return $this;
    }

    /**
     * Remove promotions
     *
     * @param \Woojin\GoodsBundle\Entity\Promotion $promotions
     */
    public function removePromotion(\Woojin\GoodsBundle\Entity\Promotion $promotions)
    {
        $this->promotions->removeElement($promotions);
    }

    /**
     * Get promotions
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPromotions()
    {
        return $this->promotions;
    }

    /**
     * Add behalfs
     *
     * @param \Woojin\GoodsBundle\Entity\Behalf $behalfs
     * @return User
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
     * Get behalfs
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getClues()
    {
        return $this->clues;
    }

    /**
     * Get holidays
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getHolidays()
    {
        return $this->holidays;
    }
}
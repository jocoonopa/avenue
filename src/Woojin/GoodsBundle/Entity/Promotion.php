<?php

namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Promotion
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Woojin\GoodsBundle\Entity\PromotionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Promotion
{
    const GS_SOLD = 2;

    /**
     * @Exclude
     * @ORM\ManyToOne(targetEntity="\Woojin\UserBundle\Entity\User", inversedBy="promotions")
     * @var User
     */
    protected $user;

    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="promotion")
     * 
     * @var GoodsPassports[]
     */
    protected $goodsPassports;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *     message = "活動名稱不可空白"
     * )
     * @ORM\Column(name="name", type="string", length=150)
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\NotBlank(
     *     message = "活動敘述不可空白"
     * )
     * @ORM\Column(name="description", type="text")
     */
    private $description;

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
     * @ORM\Column(name="start_at", type="datetime")
     */
    private $startAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stop_at", type="datetime")
     */
    private $stopAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_timeliness", type="boolean")
     */
    private $isTimeliness;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_display", type="boolean")
     */
    private $isDisplay;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="smallint")
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="thread", type="integer")
     */
    private $thread;

    /**
     * @var float
     *
     * @Assert\LessThanOrEqual(
     *     value = 1,
     *     message = "折扣必須為不超過 1 的數字"
     * )
     * @ORM\Column(name="discount", type="float")
     */
    private $discount;

    /**
     * @var integer
     *
     * @ORM\Column(name="gift", type="integer")
     */
    private $gift;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_stack", type="boolean")
     */
    private $isStack;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * @ORM\Column(type="integer")
     */
    protected $count;

    /**
     * @ORM\Column(type="integer")
     */
    protected $womenCount;

    /**
     * @ORM\Column(type="integer")
     */
    protected $menCount;

    /**
     * @ORM\Column(type="integer")
     */
    protected $secondhandCount;

    /**
     * @ORM\Column(type="integer")
     */
    protected $sort;

    /**
     * Alias of getStopDiff()
     * 
     * @return integer
     */
    public function getDiff()
    {
        return $this->getStopDiff();    
    }

    /**
     * 活動結束時間 - 現在時間
     *
     * >= 0 表示活動尚未結束
     * 
     * @return integer
     */
    public function getStopDiff()
    {
        $now = new \DateTime();
        $stopAt = $this->getStopAt();
        $interval = $now->diff($stopAt);

        return (int) $interval->format('%R%a');
    }

    /**
     * 活動開始時間 - 現在時間
     *
     * <= 0 表示活動已經開始
     * 
     * @return integer
     */
    public function getStartDiff()
    {
        $now = new \DateTime();
        $startAt = $this->getStartAt();
        $interval = $now->diff($startAt);

        return ((int) $interval->format('%R%a'));
    }

    /**
     * 是否在活動期間
     * 
     * @return boolean
     */
    public function isInTimeRegion()
    {
        return ($this->getStopDiff() >= 0) && ($this->getStartDiff() <= 0);
    }

    /**
     * 判斷活動是否可顯示
     * 
     * @return boolean
     */
    public function isValid()
    {
        return $this->isInTimeRegion() && $this->isDisplay;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        // check if we have an old image path
        if (isset($this->path)) {
            // store the old name to delete after the update
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir() . '/' . $this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : '/' . $this->getUploadDir() . '/' . $this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__ . '/../../../../web/' . $this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw up
        // when displaying uploaded doc/image in the view.
        return 'img/promotion';
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
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->getFile()) {
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename . '.' . $this->getFile()->guessExtension();
        }
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePath();
        if ($file) {
            unlink($file);
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->getFile()->move($this->getUploadRootDir(), $this->path);

        // check if we have an old image
        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }

    /**
     * 檢查活動存在已經售出的商品
     *
     * ========== 商業邏輯 ============
     * 
     * 如果存在已經售出的商品
     *     活動不允許做任何修改已避免無謂糾紛
     *     活動不可刪除 
     * 
     * @return array
     */
    public function hasSold()
    {
        $iterator = $this->getGoodsPassports()->getIterator();

        while ($iterator->valid()) {
            $product = $iterator->current();

            if ($product->getStatus()->getId() === self::GS_SOLD) {
                return true;
            }

            $iterator->next();
        }

        return false;
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
     * @return Promotion
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
     * Set description
     *
     * @param string $description
     * @return Promotion
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return Promotion
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
     * @return Promotion
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
     * Set startAt
     *
     * @param \DateTime $startAt
     * @return Promotion
     */
    public function setStartAt($startAt)
    {
        $this->startAt = $startAt;
    
        return $this;
    }

    /**
     * Get startAt
     *
     * @return \DateTime 
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * Set stopAt
     *
     * @param \DateTime $stopAt
     * @return Promotion
     */
    public function setStopAt($stopAt)
    {
        $this->stopAt = $stopAt;
    
        return $this;
    }

    /**
     * Get stopAt
     *
     * @return \DateTime 
     */
    public function getStopAt()
    {
        return $this->stopAt;
    }

    /**
     * Set isTimeliness
     *
     * @param boolean $isTimeliness
     * @return Promotion
     */
    public function setIsTimeliness($isTimeliness)
    {
        $this->isTimeliness = $isTimeliness;
    
        return $this;
    }

    /**
     * Get isTimeliness
     *
     * @return boolean 
     */
    public function getIsTimeliness()
    {
        return $this->isTimeliness;
    }

    /**
     * Set isDisplay
     *
     * @param boolean $isDisplay
     * @return Promotion
     */
    public function setIsDisplay($isDisplay)
    {
        $this->isDisplay = $isDisplay;
    
        return $this;
    }

    /**
     * Get isDisplay
     *
     * @return boolean 
     */
    public function getIsDisplay()
    {
        return $this->isDisplay;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Promotion
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set thread
     *
     * @param integer $thread
     * @return Promotion
     */
    public function setThread($thread)
    {
        $this->thread = $thread;
    
        return $this;
    }

    /**
     * Get thread
     *
     * @return integer 
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Set discount
     *
     * @param float $discount
     * @return Promotion
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    
        return $this;
    }

    /**
     * Get discount
     *
     * @return float 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set gift
     *
     * @param integer $gift
     * @return Promotion
     */
    public function setGift($gift)
    {
        $this->gift = $gift;
    
        return $this;
    }

    /**
     * Get gift
     *
     * @return integer 
     */
    public function getGift()
    {
        return $this->gift;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->goodsPassports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->type = 0;
        $this->gift = 0;
        $this->thread = 0; 
        $this->isTimeliness = false;
        $this->count = 0;
        $this->womenCount = 0;
        $this->menCount = 0;
        $this->secondhandCount = 0;
        $this->sort = 10;
        $this->description = '活動描述';
    }
    
    /**
     * Add goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     * @return Promotion
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
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return Promotion
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
     * Set path
     *
     * @param string $path
     * @return Promotion
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set count
     *
     * @param integer $count
     * @return Promotion
     */
    public function setCount($count)
    {
        $this->count = $count;
    
        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set womenCount
     *
     * @param integer $womenCount
     * @return Promotion
     */
    public function setWomenCount($womenCount)
    {
        $this->womenCount = $womenCount;
    
        return $this;
    }

    /**
     * Get womenCount
     *
     * @return integer 
     */
    public function getWomenCount()
    {
        return $this->womenCount;
    }

    /**
     * Set menCount
     *
     * @param integer $menCount
     * @return Promotion
     */
    public function setMenCount($menCount)
    {
        $this->menCount = $menCount;
    
        return $this;
    }

    /**
     * Get menCount
     *
     * @return integer 
     */
    public function getMenCount()
    {
        return $this->menCount;
    }

    /**
     * Set secondhandCount
     *
     * @param integer $secondhandCount
     * @return Promotion
     */
    public function setSecondhandCount($secondhandCount)
    {
        $this->secondhandCount = $secondhandCount;
    
        return $this;
    }

    /**
     * Get secondhandCount
     *
     * @return integer 
     */
    public function getSecondhandCount()
    {
        return $this->secondhandCount;
    }

    /**
     * Set isStack
     *
     * @param boolean $isStack
     * @return Promotion
     */
    public function setIsStack($isStack)
    {
        $this->isStack = $isStack;
    
        return $this;
    }

    /**
     * Get isStack
     *
     * @return boolean 
     */
    public function getIsStack()
    {
        return $this->isStack;
    }

    /**
     * Set sort
     *
     * @param integer $sort
     * @return Promotion
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    
        return $this;
    }

    /**
     * Get sort
     *
     * @return integer 
     */
    public function getSort()
    {
        return $this->sort;
    }
}
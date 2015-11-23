<?php 
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table(name="img")
 */
class Img
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="img")
     * @var GoodsPassport[]
     */
    protected $goods_passport;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $yahooName;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->goods_passport = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get goods_passport
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProducts()
    {
        return $this->goods_passport;
    }

    public function getPurePath()
    {
        return str_replace($this->getName(), '', $this->path);
    }

    public function getPathNoBorder($isAbs = false)
    {
        return (($isAbs) ? $_SERVER['DOCUMENT_ROOT'] : '') .  substr($this->path, 0, -4) . '_' . $this->getProducts()->last()->getSn() . '.jpg'; 
    }

    /**
     * 取得圖片檔案名稱
     * 
     * @return string
     */
    public function getName()
    {
        $pathParts = explode('/', $this->path);

        return (is_array($pathParts)) ? $pathParts[count($pathParts) - 1] : null;
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
     * Set path
     *
     * @param string $path
     * @return Img
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
    public function getPath($isAbs = false)
    {
        return ($isAbs) ? $_SERVER['DOCUMENT_ROOT'] . $this->path : $this->path;
        //return 'http://avenue2003.29230696.com' . $this->path;
    }

    /**
     * Add goods_passport
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport
     * @return Img
     */
    public function addGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport)
    {
        $this->goods_passport[] = $goodsPassport;
    
        return $this;
    }

    /**
     * Remove goods_passport
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport
     */
    public function removeGoodsPassport(\Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassport)
    {
        $this->goods_passport->removeElement($goodsPassport);
    }

    /**
     * Get goods_passport
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoodsPassport()
    {
        return $this->goods_passport;
    }

    /**
     * Set yahooName
     *
     * @param string $yahooName
     * @return Img
     */
    public function setYahooName($yahooName)
    {
        $this->yahooName = $yahooName;
    
        return $this;
    }

    /**
     * Get yahooName
     *
     * @return string 
     */
    public function getYahooName()
    {
        return $this->yahooName;
    }
}
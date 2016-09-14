<?php 
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table(name="desimg")
 */
class Desimg
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="desimg")
     * @var GoodsPassports[]
     */
    protected $goodsPassports;

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
        $this->goodsPassports = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getPurePath()
    {
        return str_replace($this->getName(), '', $this->path);
    }

    public function getSplitPath($i)
    {
        return substr($this->path, 0, -4) . $i . '.jpg';
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
     * @return Desimg
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
        return 'http://avenue2003.29230696.com' . $this->path;
    }

    public function _getPath()
    {
        return $this->path;
    }

    /**
     * Add goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     * @return Desimg
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
     * Set yahooName
     *
     * @param string $yahooName
     * @return Desimg
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

<?php 
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class Brief
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="brief")
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
     * @ORM\Column(type="text")
     */
    protected $content;
    /**
     * Constructor
     */
    public function __construct($content)
    {
        $this->goodsPassports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->content = $content;
    }

    public function getSubstrBriefLimit($limitLength = 255)
    {
        $strCount = 0;
        $rows = explode("\n", $this->content);
        
        foreach ($rows as $key => $row) {
            if (($strCount + mb_strlen($row, 'utf-8')) > $limitLength) {
                return $strCount;
            }

            $strCount += mb_strlen($row, 'utf-8');
        }

        return $strCount;
    }

    public function getBriefInLimit($limitLength = 255, $deli = ' ')
    {
        $strCount = 0;
        $str = '';
        $rows = explode("\n", $this->content);
        
        foreach ($rows as $key => $row) {
            if (($strCount + mb_strlen($row, 'utf-8')) > $limitLength) {
                return $str;
            }

            $strCount += mb_strlen($row, 'utf-8');
            $str.= $deli . $row;
        }

        return $str;
    }

    public function getBriefFillRow($limitLength = 50)
    {
        $deli = '%%%###';

        $str = $this->getBriefInLimit($limitLength, $deli);

        return str_replace($deli, '', $str);
    }

    public function getOneBrief($rowNum)
    {
        $rows = explode("\n", $this->content);

        return isset($rows[($rowNum - 1)]) ? $rows[($rowNum - 1)] : null;
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
     * Add goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     * @return Brief
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
     * Set content
     *
     * @param string $content
     * @return Brief
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }
}

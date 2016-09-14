<?php 
namespace Woojin\GoodsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;

/**
 * @ORM\Entity
 * @ORM\Table()
 */
class Description
{
    /**
     * @Exclude
     * @ORM\OneToMany(targetEntity="GoodsPassport", mappedBy="description")
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
     * @ORM\Column(type="text", nullable=true)
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
     * Set content
     *
     * @param string $content
     * @return Description
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

    /**
     * Add goodsPassports
     *
     * @param \Woojin\GoodsBundle\Entity\GoodsPassport $goodsPassports
     * @return Description
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
}

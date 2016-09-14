<?php

namespace Woojin\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Woojin\Service\Sculper\Prototype\Type;


/**
 * AvenueClue
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 */
class AvenueClue
{
    /**
    * @ORM\ManyToOne(targetEntity="User", inversedBy="clues")
    * @var User
    */
    private $user;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_at", type="datetime")
     */
    private $createAt;

    /**
     * @var array
     *
     * @ORM\Column(name="content", type="json_array")
     */
    private $content;

    /**
     * 將陣列內容轉換成html字串，方便在模版使用
     * 
     * @return string
     */
    public function getHTMLContent()
    {
        $html = '';

        switch ($this->content['type'])
        {
            case Type::CANCEL_CONSIGN_TO_PURCHASE:
                $html .= '(收購轉寄賣) ';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',成本: ' . number_format($this->content['product']['before_cost']) . '->' . number_format($this->content['product']['after_cost']);
                $html .= ',售價: ' . number_format($this->content['product']['before_price']) . '->' . number_format($this->content['product']['after_price']);

                break;

            case Type::CANCEL_PURCHASE_IN:
                $html .= '(取消進貨) ';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',成本: ' . $this->content['product']['cost'];
                $html .= ',售價: ' . $this->content['product']['price'];

                break;

            case Type::CANCEL_SOLDOUT:
                $html .= '(取消售出) ';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',應付: ' . number_format($this->content['order']['required']);
                $html .= ',已付: ' . number_format($this->content['order']['paid']);
                $html .= ',狀態: ' . $this->content['order']['status'];

                break;

            case Type::CONSIGN_TO_PURCHASE:
                $html .= '(寄賣轉收購) ';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',成本: ' . number_format($this->content['product']['before_cost']) . '->' . number_format($this->content['product']['after_cost']);
                $html .= ',售價: ' . number_format($this->content['product']['before_price']) . '->' . number_format($this->content['product']['after_price']);

                break;

            case Type::MODIFY_OPE_DATETIME:
                $html .= '(修改操作時間) ';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',時間: ' . $this->content['ope']['before_datetime'] . '->' . $this->content['ope']['after_datetime'];

                break;

            case Type::PATCH:
                $html .= '(補款) ';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',應付: ' . number_format($this->content['order']['required']);
                $html .= ',已付: ' . number_format($this->content['order']['paid']);
                $html .= ',狀態: ' . $this->content['order']['status'];
                $html .= ',付費方式: ' . $this->content['ope']['pay_type'];
                $html .= ',金額: ' . $this->content['ope']['amount'];

                break;

            case Type::PRODUCT_MODIFY:
                $html .= '(修改商品資訊) ';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',成本: ' . number_format($this->content['product']['before_cost']) . '->' . number_format($this->content['product']['after_cost']);
                $html .= ',售價: ' . number_format($this->content['product']['before_price']) . '->' . number_format($this->content['product']['after_price']);

                break;

            case Type::PURCHASE_IN:
                $html .= (array_key_exists('custom', $this->content['product']) && $this->content['product']['custom'] !== '') 
                    ? '(客戶寄賣)' : '(一般進貨)';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',應付: ' . number_format($this->content['order']['required']);
                $html .= ',已付: ' . number_format($this->content['order']['paid']);
                $html .= ',狀態: ' . $this->content['order']['status'];

                break;

            case Type::SOLDOUT:
                $html .= '(' . (isset($this->content['order']['kind']) ? $this->content['order']['kind'] : '售出') . ')';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',應付: ' . number_format($this->content['order']['required']);
                $html .= ',已付: ' . number_format($this->content['order']['paid']);
                $html .= ',付費方式: ' . $this->content['order']['pay_type'];
                $html .= ',狀態: ' . $this->content['order']['status'];

                break;

            case Type::TURNOUT:
                $html .= '(換貨)';
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',應付: ' . number_format($this->content['order']['required']);
                $html .= ',已付: ' . number_format($this->content['order']['paid']);
                $html .= ',付費方式: ' . $this->content['order']['pay_type'];
                $html .= ',狀態: ' . $this->content['order']['status'];

                break;

            case Type::CUSTOM_GETBACK:
                $html .= '(客寄取回)' . $this->content['product']['custom'];
                
                $html .= ' ' . $this->content['product']['brand'] . ' ';
                $html .= ' ' . $this->content['product']['name'] . ' ';
                $html .= ',應付: ' . $this->content['order']['required'];
                $html .= ',已付: ' . $this->content['order']['paid'];

                break;

            default:
                break;
        }

        return $html;
    }

    /**
     * @ORM\PrePersist
     */
    public function autoSetCreateAt()
    {
        $this->setCreateAt(new \Datetime());
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
     * Set createAt
     *
     * @param \DateTime $createAt
     * @return AvenueClue
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
     * Set content
     *
     * @param array $content
     * @return AvenueClue
     */
    public function setContent($content)
    {
        $this->content = $content;
    
        return $this;
    }

    /**
     * Get content
     *
     * @return array 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set user
     *
     * @param \Woojin\UserBundle\Entity\User $user
     * @return AvenueClue
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
}

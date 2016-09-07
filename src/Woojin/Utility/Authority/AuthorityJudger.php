<?php

namespace Woojin\Utility\Authority;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class AuthorityJudger
{
    const ROLE_CHIEF = 2;
    const ROLE_BOSS = 3;
    const ROLE_FINANCIAL = 7;

    protected $context;

    protected $user;

    public function __construct(TokenStorage $context)
    {
        $this->context = $context;
        $this->user = $this->context->getToken()->getUser();

        if (!$this->user || !is_object($this->user)) {
            $this->store = null;
        } else {
            $this->store = $this->user->getStore();
        }
    }

    public function isOwnStore($cellSn)
    {
        $storeSn = $this->store->getSn();

        return (substr($cellSn, 0, 1) === $storeSn);
    }   

    public function isOwn(){}

    public function hasAuth($targetName)
    {
        return $this->user->getRole()->hasAuth($targetName);
    }

    /**
     * 判斷是否有足夠權限瀏覽商品成本 [本店判斷在別的地方進行]
     * 
     * @return boolean
     */
    public function isCostValid()
    {
        return ($this->user) 
            && is_object($this->user) 
            && in_array(
                $this->user->getTheRoles()->getId(), 
                array(
                    self::ROLE_CHIEF, 
                    self::ROLE_BOSS, 
                    self::ROLE_FINANCIAL
                ))
            ;
    }

    public function getUser()
    {
        return $this->user;
    }
}
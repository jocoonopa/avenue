<?php

namespace Woojin\Utility\Helper;

use Woojin\OrderBundle\Entity\Invoice;
use Woojin\OrderBundle\Entity\BenefitFrag;
use Woojin\OrderBundle\Entity\BenefitEvent;

class BenefitHelper implements IHelper
{
    protected $em;
    
    protected $session;

    protected $remain;
    
    protected $invoice;
    
    protected $benefits;

    protected $event;

    protected $rules;

    protected $ruleHelper;

    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session, \Doctrine\ORM\EntityManager $em)
    {
        $this->session = $session;

        $this->em = $em;

        $this->event = $this->em->getRepository('WoojinOrderBundle:Benefit')->findNowUsingEvent();

        if ($this->event) {
           $this->setEvent(); 
        
            if ($this->event) {
                $this->setRules($this->event->getRules());
            }

            $this
                ->setBenefits($this->em->getRepository('WoojinOrderBundle:Invoice')->findOwn($this->invoice->getCustom()))
            ; 
        }

        $this->ruleHelper = new BenefitRuleHelper($this->rules);
    }

    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    public function setEvent(BenefitEvent $event)
    {
        $this->event = $event;

        return $this;
    }

    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    public function setBenefits($benefits)
    {
        $this->benefits = $benefits;

        return $this;
    }

    public function getBenefits()
    {
        return $this->benefits;
    }

    public function idealPredict(Invoice $invoice)
    {
        $ideal = 0;

        foreach ($invoice->getOrders() as $order) {
            if ($order->getFits()) {
                continue;
            }

            $ideal += $this->ruleHelper->getPredict($order);
        }
        
        return $ideal;
    }

    public function realPredict()
    {

    }

    public function carve(Invoice $invoice)
    {
        $this->setInvoice($invoice);
    }

    /**
     * 切換倒下一個可用的 Benefit 實體
     * 
     * @return \Woojin\OrderBundle\Entity\Benefit
     */
    public function next()
    {

    }

    public function split()
    {

    }
}
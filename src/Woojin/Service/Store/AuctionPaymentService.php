<?php

namespace Woojin\Service\Store;

use Woojin\StoreBundle\Entity\AuctionPayment;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuctionPaymentService
{
    use Traits\AuctionPaymentServiceOptionsTrait;

    protected $resolver;

    public function create(array $options)
    {
        $this->configureCreateOptions();
        $options = $this->getResolver()->resolve($options);

        $payment = new AuctionPayment;
        $payment
            ->setAuction($options['auction'])
            ->setPayType($options['payType'])
            ->setCreater($options['creater'])
            ->setAmount($options['amount'])
            ->setPaidAt($options['paidAt'])
            ->setOrgAmount($options['orgAmount'])
        ;

        return $payment;
    }

    public function update(AuctionPayment $payment, array $options)
    {
        $this->configureUpdateOptions($payment);

        $options = $this->getResolver()->resolve($options);

        $payment
            ->attachUpdatePaidAtMemo($options['updater'], $options['paidAt'])
            ->setPaidAt($options['paidAt'])
        ;

        return $payment;
    }

    public function drop(AuctionPayment $payment, array $options)
    {
        $this->configureDropOptions($payment);

        $options = $this->getResolver()->resolve($options);

        $payment
            ->setIsCancel(true)
            ->setCancelAt(new \DateTime)
            ->setCanceller($options['canceller'])
        ;

        return $payment;
    }

    protected function setResolver(OptionsResolver $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    public function getResolver()
    {
        return $this->resolver;
    }
}

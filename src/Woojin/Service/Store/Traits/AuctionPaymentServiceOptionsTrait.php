<?php

namespace Woojin\Service\Store\Traits;

use Woojin\StoreBundle\Entity\AuctionPayment;
use Woojin\UserBundle\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

trait AuctionPaymentServiceOptionsTrait
{
    protected function configureCreateOptions()
    {
        $this->setResolver(new OptionsResolver);

        $this->getResolver()
            ->setDefault('paidAt', new \DateTime)
            ->setDefault('memo', NULL)
            ->setRequired('auction')
            ->setRequired('payType')
            ->setRequired('creater')
            ->setRequired('amount')
            ->setAllowedTypes('auction', 'Woojin\StoreBundle\Entity\Auction')
            ->setAllowedTypes('payType', 'Woojin\OrderBundle\Entity\payType')
            ->setAllowedTypes('creater', 'Woojin\UserBundle\Entity\User')
            ->setAllowedTypes('amount', 'integer')
            ->setAllowedTypes('paidAt', 'DateTime')
            ->setAllowedValues('amount', function ($amount) {
                return 0 <= $amount;
            })
            ->setNormalizer('amount', function (Options $options, $value) {
                if ($options['auction']->getPrice() < $value) {
                    throw new \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException('Amount cannot larger than auction price!');
                }

                return $value;
            })
            ->setDefault('orgAmount', function (Options $options) {
                return (int) ($options['amount'] * $options['payType']->getDiscount());
            })
        ;
    }

    protected function configureUpdateOptions(AuctionPayment $payment)
    {
        $this->setResolver(new OptionsResolver);

        $this->getResolver()
            ->setRequired('paidAt')
            ->setRequired('updater')
            ->setAllowedTypes('updater', 'Woojin\UserBundle\Entity\User')
            ->setAllowedValues('updater', function (User $updater) use ($payment) {
                return $updater->getStore()->getId() === $payment->getAuction()->getBsoStore()->getId();
            })
        ;
    }

    protected function configureDropOptions(AuctionPayment $payment)
    {
        $this->setResolver(new OptionsResolver);

        $this->getResolver()
            ->setRequired('canceller')
            ->setAllowedTypes('canceller', 'Woojin\UserBundle\Entity\User')
            ->setAllowedValues('canceller', function (User $canceller) use ($payment) {
                return $canceller->getStore()->getId() === $payment->getAuction()->getBsoStore()->getId();
            })
        ;
    }
}

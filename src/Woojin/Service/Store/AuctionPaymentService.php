<?php

namespace Woojin\Service\Store;

use Woojin\StoreBundle\Entity\AuctionPayment;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

class AuctionPaymentService
{
    protected $resolver;

    public function create(array $options)
    {
        $this->configureCreateOptions();
        $this->getResolver()->resolve($options);

        $payment = new AuctionPayment;
        $payment
            ->setAuction($options['auction'])
            ->setPayType($options['payType'])
            ->setCreater($options['creater'])
            ->setAmount($options['amount'])
            ->setOrgAmount((int) ($payment->getAmount() * $payment->getPayType()->getDiscount()))
        ;

        if (array_key_exists('paidAt', $options)) {
            $payment->setPaidAt($options['paidAt']);
        }

        if (array_key_exists('memo', $options)) {
            $payment->setMemo($options['memo']);
        }

        return $payment;
    }

    public function update(array $options)
    {
        return new AuctionPayment;
    }

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
        ;
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

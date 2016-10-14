<?php

namespace spec\Woojin\Service\Store;

use Mockery as m;
use Woojin\StoreBundle\Entity\Auction;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AuctionPaymentServiceSpec extends ObjectBehavior
{
    const DISCOUNT_RATE = 0.95;
    const AUCTION_PRICE = 5000;

    protected $auction;
    protected $payType;
    protected $creater;

    public function let()
    {
        $this->auction = m::mock('\Woojin\StoreBundle\Entity\Auction', [
            'getId' => 1,
            'getPrice' => static::AUCTION_PRICE,
            'getProfitStatus' => Auction::PROFIT_STATUS_NOT_PAID_YET
        ]);
        $this->payType = m::mock('\Woojin\OrderBundle\Entity\PayType', ['getId' => 1, 'getDiscount' => static::DISCOUNT_RATE]);
        $this->creater = m::mock('\Woojin\UserBundle\Entity\User', ['getId' => 21, 'getUsername' => 'jocoonopa']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Woojin\Service\Store\AuctionPaymentService');
        $this->getResolver()->shouldEqual(NULL);
    }

    public function it_can_create_payment_by_given_options()
    {
        $options = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'creater' => $this->creater,
            'paidAt' => new \DateTime('2001-01-01 00:00:00'),
            'amount' => 1000,
            'memo' => 'test'
        ];

        $payment = $this->create($options);

        $payment->shouldHaveType('Woojin\StoreBundle\Entity\AuctionPayment');
        $payment->getAuction()->getId()->shouldEqual($this->auction->getId());
        $payment->getPayType()->getId()->shouldEqual($this->payType->getId());
        $payment->getPayType()->getDiscount()->shouldEqual(static::DISCOUNT_RATE);
        $payment->getCreater()->getId()->shouldEqual($this->creater->getId());
        $payment->getAmount()->shouldEqual($options['amount']);
        $payment->getOrgAmount()->shouldEqual((int) ($options['amount'] * static::DISCOUNT_RATE));
        $payment->getPaidAt()->shouldHaveType('DateTime');
        $payment->getPaidAt()->format('Y')->shouldEqual('2001');
        $payment->getMemo()->shouldEqual('test');
    }

    public function it_should_check_create_options_in_create()
    {
        $options_1 = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'amount' => 1000
        ];

        $options_2 = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'creater' => $this->creater,
            'amount' => '1000'
        ];

        $options_3 = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'creater' => $this->creater,
            'amount' => -100
        ];

        $options_4 = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'creater' => $this->creater,
            'amount' => 0
        ];

        $options_5 = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'creater' => $this->creater,
            'amount' => static::AUCTION_PRICE
        ];

        $options_6 = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'creater' => $this->creater,
            'amount' => 5001
        ];

        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\MissingOptionsException')->during('create', array($options_1));
        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')->during('create', array($options_2));
        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')->during('create', array($options_3));
        $this->create($options_4)->shouldHaveType('Woojin\StoreBundle\Entity\AuctionPayment');
        $this->create($options_5)->shouldHaveType('Woojin\StoreBundle\Entity\AuctionPayment');
        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')->during('create', array($options_6));
    }

    public function it_update_payment_memo_and_paid_at_by_options()
    {
        $options = [
        ];

        $payment = $this->update($options);

        $payment->shouldHaveType('Woojin\StoreBundle\Entity\AuctionPayment');
    }

    public function letGo()
    {
        m::close();
    }
}

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
    const BSO_STORE_ID = 1;
    const PAYMENT_ID = 1;

    protected $auction;
    protected $payType;
    protected $creater;
    protected $payment;

    public function let()
    {
        $this->auction = m::mock('\Woojin\StoreBundle\Entity\Auction', [
            'getId' => 1,
            'getOwe' => static::AUCTION_PRICE,
            'getProfitStatus' => Auction::PROFIT_STATUS_NOT_PAID_YET
        ]);
        $this->payType = m::mock('\Woojin\OrderBundle\Entity\PayType', ['getId' => 1, 'getDiscount' => static::DISCOUNT_RATE]);
        $this->creater = m::mock('\Woojin\UserBundle\Entity\User', ['getId' => 21, 'getUsername' => 'jocoonopa']);

        $this->payment = m::mock('\Woojin\StoreBundle\Entity\AuctionPayment')->makePartial();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Woojin\Service\Store\AuctionPaymentService');
        $this->getResolver()->shouldEqual(NULL);
    }

    public function it_can_create_payment_by_given_options()
    {
        $this->creater->shouldReceive('getStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->auction->shouldReceive('getBsoStore->getId')->andReturn(static::BSO_STORE_ID);

        $options = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'creater' => $this->creater,
            'paidAt' => new \DateTime('2001-01-01 00:00:00'),
            'amount' => 1000
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
        $payment->getPaidAt()->format('Y-m-d H:i:s')->shouldEqual('2001-01-01 00:00:00');
        $payment->getMemo()->shouldEqual(NULL);
    }

    public function it_should_check_amount_is_valid_in_create()
    {
        $this->creater->shouldReceive('getStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->auction->shouldReceive('getBsoStore->getId')->andReturn(static::BSO_STORE_ID);

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

    public function it_should_check_creater_is_allowed_to_create_payment_for_this_auction()
    {
        $this->creater->shouldReceive('getStore->getId')->andReturn(999);
        $this->auction->shouldReceive('getBsoStore->getId')->andReturn(static::BSO_STORE_ID);

        $options = [
            'auction' => $this->auction,
            'payType' => $this->payType,
            'creater' => $this->creater,
            'amount' => 1000
        ];

        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')->during('create', array($options));
    }

    /**
     * The update log would record at attribute memo
     *
     * Amount is not allowed to edit, the user should cancel and re create one
     */
    public function it_update_payment_paidat_with_options()
    {
        $this->creater->shouldReceive('getStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->payment->shouldReceive('getAuction->getBsoStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->payment->shouldReceive('setPaidAt')->passthru();
        $this->payment->shouldReceive('getPaidAt')->andReturn(new \DateTime('2002-02-02 00:00:00'), new \DateTime('2020-12-12 00:00:00'));
        $this->payment->shouldReceive('getMemo')->passthru();

        $options = [
            'paidAt' => new \DateTime('2020-12-12 00:00:00'),
            'updater' => $this->creater
        ];

        $payment = $this->update($this->payment, $options);

        $payment->shouldHaveType('Woojin\StoreBundle\Entity\AuctionPayment');
        $payment->getPaidAt()->shouldHaveType('DateTime');
        $payment->getPaidAt()->format('Y-m-d H:i:s')->shouldEqual('2020-12-12 00:00:00');
        $payment->getMemo()->shouldBeString();
        $payment->getMemo()->shouldStartWith('原付款時間2002-02-02');
        $payment->getMemo()->shouldEndWith('更新為2020-12-12<br>');
    }

    public function it_validate_updater_when_update_with_options()
    {
        $this->creater->shouldReceive('getStore->getId')->andReturn(999);
        $this->payment->shouldReceive('getAuction->getBsoStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->payment->shouldReceive('setPaidAt')->passthru();
        $this->payment->shouldReceive('getPaidAt')->andReturn(new \DateTime('2002-02-02 00:00:00'), new \DateTime('2020-12-12 00:00:00'));
        $this->payment->shouldReceive('getMemo')->passthru();

        $options = [
            'paidAt' => new \DateTime('2020-12-12 00:00:00'),
            'updater' => $this->creater
        ];

        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')->during('update', array($this->payment, $options));
    }

    public function it_drop_given_payment_and_set_canceller_as_given()
    {
        $this->creater->shouldReceive('getStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->payment->shouldReceive('getId')->andReturn(static::PAYMENT_ID);
        $this->payment->shouldReceive('getAuction->getBsoStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->payment->shouldReceive('getAuction->getProfitStatus')->andReturn(Auction::PROFIT_STATUS_NOT_PAID_YET);

        $options = array(
            'canceller' => $this->creater
        );

        $payment = $this->drop($this->payment, $options);

        $payment->shouldHaveType('Woojin\StoreBundle\Entity\AuctionPayment');
        $payment->getId()->shouldEqual(static::PAYMENT_ID);
        $payment->getIsCancel()->shouldEqual(true);
        $payment->getCancelAt()->shouldHaveType('DateTime');
        $payment->getCanceller()->shouldHaveType('Woojin\UserBundle\Entity\User');
    }

    public function it_should_validate_canceller_when_drop()
    {
        $this->creater->shouldReceive('getStore->getId')->andReturn(999);
        $this->payment->shouldReceive('getAuction->getBsoStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->payment->shouldReceive('getAuction->getProfitStatus')->andReturn(Auction::PROFIT_STATUS_NOT_PAID_YET);

        $options = array(
            'canceller' => $this->creater
        );

        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')->during('drop', array($this->payment, $options));

        $optionsEmpty = array();

        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\MissingOptionsException')->during('drop', array($this->payment, $optionsEmpty));
    }

    public function it_should_not_allow_drop_if_auction_profit_has_assigned()
    {
        $this->creater->shouldReceive('getStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->payment->shouldReceive('getAuction->getBsoStore->getId')->andReturn(static::BSO_STORE_ID);
        $this->payment->shouldReceive('getAuction->getProfitStatus')->andReturn(Auction::PROFIT_STATUS_ASSIGN_COMPLETE);

        $options = array(
            'canceller' => $this->creater
        );

        $this->shouldThrow('Symfony\Component\OptionsResolver\Exception\InvalidOptionsException')->during('drop', array($this->payment, $options));
    }

    public function letGo()
    {
        m::close();
    }
}

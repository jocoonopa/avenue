<?php

namespace Woojin\StoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Woojin\Tests\Controller\AuthControllerTest;
use Woojin\StoreBundle\Entity\Auction;

/**
 * It's highly recommended that a functional test only tests the response
 */
class AuctionPaymentControllerTest extends AuthControllerTest
{
    protected $uri;

    public function testCreateAction()
    {
        $this->client->request('POST', '/admin/auction_payment/auction/26', array(
            'pay_type' => 1,
            'amount' => 1000,
            'paid_at' => '2016-10-10 00:00:00'
        ));
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->followRedirect();

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('競拍付款新增成功', $this->client->getResponse()->getContent());
    }

    public function testUpdateAction()
    {
        $this->client->request('POST', '/admin/auction_payment/1', array(
            '_method' => 'put',
            'paid_at' => '2016-10-10 00:00:00'
        ));
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('競拍付款時間更新完成', $this->client->getResponse()->getContent());
    }

    public function testDropAction()
    {
        $this->client->request('POST', '/admin/auction_payment/1', array(
            '_method' => 'delete'
        ));
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse()->getStatusCode());

        $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('競拍付款取消完成', $this->client->getResponse()->getContent());
    }

    public function testCreateCallbackAction()
    {
        //Create
        $this->client->request('POST', '/admin/auction_payment/auction/26?_disableTransaction=1', array(
            'pay_type' => 1,
            'amount' => 49500,
            'paid_at' => '2016-12-31'
        ));
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testAfterCreateAction()
    {
        $crawler = $this->client->request('GET', '/admin/goods/edit/158/v2');

        $this->uri = $crawler->filter('form.auction_payment_drop')->last()->attr('action');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('於2016-12-31付清', $this->client->getResponse()->getContent());

        $this->client->request('POST', "{$this->uri}?_disableTransaction=1", array(
            '_method' => 'put',
            'paid_at' => '2021-12-20 00:00:00'
        ));
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testAfterUpdateAction()
    {
        $crawler = $this->client->request('GET', '/admin/goods/edit/158/v2');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('於2021-12-20付清', $this->client->getResponse()->getContent());

        $this->uri = $crawler->filter('form.auction_payment_drop')->last()->attr('action');

        $this->client->request('POST', "{$this->uri}?_disableTransaction=1", array(
            '_method' => 'delete'
        ));
        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testAfterDropAction()
    {
        $crawler = $this->client->request('GET', '/admin/goods/edit/158/v2');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertContains('尚欠49500元', $this->client->getResponse()->getContent());
    }
}

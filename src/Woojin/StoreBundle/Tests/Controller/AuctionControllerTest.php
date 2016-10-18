<?php

namespace Woojin\StoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Woojin\Tests\Controller\AuthControllerTest;

/**
 * It's highly recommended that a functional test only tests the response
 */
class AuctionControllerTest extends AuthControllerTest
{
    public function testIndexAction()
    {
        $this->withoutHash();
        $this->withHash();
    }

    public function testUpdateShippingAction()
    {
        $crawler = $this->client->request('POST', '/admin/auction/26/shipping', array(
            '_method' => 'PUT',
            'shipping' => 1
        ));
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertArrayHasKey('option', json_decode($this->client->getResponse()->getContent(), true));

        $crawler = $this->client->request('POST', '/admin/auction/25/shipping', array(
            '_method' => 'PUT',
            'shipping' => 1
        ));
        $this->assertSame(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->request('POST', '/admin/auction/26/shipping', array(
            '_method' => 'PUT',
            'shipping' => 0
        ));
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateSoldAtAction()
    {
        $crawler = $this->client->request('POST', '/admin/auction/sold_at/26', array('_method' => 'PUT', 'sold_at' => '2016-10-10 00:00:00'));

        $this->assertEquals(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $crawler = $this->client->request('POST', '/admin/auction/sold_at/999999', array('_method' => 'PUT', 'sold_at' => '2016-10-10 00:00:00'));
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());

        $crawler = $this->client->request('POST', '/admin/auction/sold_at/25', array('_method' => 'PUT', 'sold_at' => '2016-10-10 00:00:00'));
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }

    public function testExportProfitAction()
    {
        $crawler = $this->client->request('POST', '/admin/auction/export_profit', array());

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'text/vnd.ms-excel; charset=utf-8'), $this->client->getResponse()->headers->get('Content-Type'));
    }

    protected function withoutHash()
    {
        $crawler = $this->client->request('GET', '/admin/auction');

        $this->assertContains('Redirecting', $this->client->getResponse()->getContent());
        $this->assertEquals(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());

        $crawler = $this->client->followRedirect();
        $this->assertGreaterThan(0, $crawler->filter('title:contains("競拍刷入頁面")')->count());
        $this->assertGreaterThan(0, $crawler->filter('div.slide')->count());
    }

    protected function withHash()
    {
        $crawler = $this->client->request('GET', '/admin/auction/#/auction');

        $this->assertGreaterThan(0, $crawler->filter('title:contains("競拍刷入頁面")')->count());
        $this->assertGreaterThan(0, $crawler->filter('div.slide')->count());
    }

    public function testTemplateListAction()
    {
        $crawler = $this->client->request('GET', '/admin/auction/template/list');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'response status is 2xx');
        $this->assertGreaterThan(0, $crawler->filter('html:contains("刷入BSO")')->count());
    }
}

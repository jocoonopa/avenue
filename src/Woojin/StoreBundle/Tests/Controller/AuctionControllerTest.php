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

<?php

namespace Woojin\StoreBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Woojin\Tests\Controller\AuthControllerTest;

/**
 * It's highly recommended that a functional test only tests the response
 */
class PlayGroundControllerTest extends AuthControllerTest
{
	public function testGoogleAction()
    {
        $crawler = $this->client->request('GET', 'https://www.yahoo.com.tw');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());

        $this->assertContains('您的瀏覽器過舊', $this->client->getResponse()->getContent());
    }
}
<?php

namespace Woojin\ApiBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Woojin\Tests\Controller\AuthControllerTest;

/**
 * It's highly recommended that a functional test only tests the response
 */
class PayTypeControllerTest extends AuthControllerTest
{
    public function testListAction()
    {
        $crawler = $this->client->request('GET', "/api/v1/paytype");
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(is_array($responseArr));
        $this->assertEquals('現金', $responseArr[0]['name']);
    }
}

<?php

namespace Woojin\ApiBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Woojin\Tests\Controller\AuthControllerTest;
use Woojin\Utility\Avenue\Avenue;

/**
 * It's highly recommended that a functional test only tests the response
 */
class AuctionControllerTest extends AuthControllerTest
{
    public function testListFilterAction()
    {
        $crawler = $this->client->request('POST', '/api/v1/auction_filter', array());
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertTrue(isset($responseArr[0]['id']), $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testShowAction()
    {
        $crawler = $this->client->request('GET', '/api/v1/auction/1');
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/api/v1/auction/999999999');
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());
    }

    public function testFindBySnAction()
    {
        $crawler = $this->client->request('GET', '/api/v1/auction/show/Y102031380052');

        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());

        $crawler = $this->client->request('GET', '/api/v1/auction/show/Y102031380052__');

        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());
    }

    public function testListAction()
    {
        $crawler = $this->client->request('GET', '/api/v1/auction');
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertTrue(is_array($responseArr), $this->client->getResponse()->getContent());
    }

    public function testNewAction()
    {
        $crawler = $this->client->request('POST', '/api/v1/auction', array('sn' => 'Y000004250091'));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertTrue(array_key_exists('status', $responseArr));
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status']);
        $this->assertTrue(array_key_exists('auction', $responseArr) && array_key_exists('product', $responseArr['auction']) && array_key_exists('status', $responseArr['auction']['product']));
        $this->assertSame(Avenue::GS_BSO_ONBOARD, $responseArr['auction']['product']['status']['id']);
        $this->assertFalse(array_key_exists('yahoo_id', $responseArr['auction']['product']));
    }

    public function testBackAction()
    {
        $crawler = $this->client->request('POST', '/api/v1/auction/back', array('_method' => 'PUT', 'sn' => 'Y000004250091'));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(array_key_exists('status', $responseArr), $this->client->getResponse()->getContent());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());

        $crawler = $this->client->request('POST', '/api/v1/auction/back', array('_method' => 'PUT', 'sn' => 'Y102031380052'));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(array_key_exists('status', $responseArr), $this->client->getResponse()->getContent());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());

    }

    public function testSoldAction()
    {
        $price = 500;
        $crawler = $this->client->request('POST', '/api/v1/auction/sold', array('_method' => 'PUT', 'sn' => 'Y102031380052', 'price' => $price));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertTrue(array_key_exists('status', $responseArr), $this->client->getResponse()->getContent());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());

        $this->assertTrue(array_key_exists('auction', $responseArr), $this->client->getResponse()->getContent());

        $crawler = $this->client->request('POST', '/api/v1/auction/sold', array('_method' => 'PUT', 'sn' => 'Y000004250091', 'price' => $price));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertTrue(array_key_exists('status', $responseArr), $this->client->getResponse()->getContent());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());

        $stringPrice = 'isNotNumber';
        $crawler = $this->client->request('POST', '/api/v1/auction/sold', array('_method' => 'PUT', 'sn' => 'Y102031380052', 'price' => $stringPrice));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertTrue(array_key_exists('status', $responseArr), $this->client->getResponse()->getContent());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_NOT_ACCEPTABLE, $responseArr['http_status_code']);
    }

    public function testCancelAction()
    {
        $crawler = $this->client->request('POST', '/api/v1/auction/cancel', array('_method' => 'PUT', 'sn' => 'Y102031380052'));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertTrue(array_key_exists('status', $responseArr), $this->client->getResponse()->getContent());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());

        $crawler = $this->client->request('POST', '/api/v1/auction/cancel', array('_method' => 'PUT', 'sn' => 'Y001159880270'));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertTrue(array_key_exists('status', $responseArr), $this->client->getResponse()->getContent());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());
    }
}

<?php

namespace Woojin\ApiBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Woojin\Tests\Controller\AuthControllerTest;
use Woojin\Utility\Avenue\Avenue;

/**
 * It's highly recommended that a functional test only tests the response
 */
class AuctionCustomControllerTest extends AuthControllerTest
{
    public function testListAction()
    {
        $mobil = '00000';
        $crawler = $this->client->request('GET', "/api/v1/auction_custom/list?mobil={$mobil}");
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame($mobil, $responseArr['custom']['mobil'], $this->client->getResponse()->getContent());
    }

    public function testShowAction()
    {
        $id = 1;
        $crawler = $this->client->request('GET', "/api/v1/auction_custom/{$id}");
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame($id, $responseArr['custom']['id'], $this->client->getResponse()->getContent());
    }

    public function testNewAction()
    {
        $crawler = $this->client->request('POST', "/api/v1/auction_custom/json", array(
            'name' => 'BSO_TEST_CUSTOM',
            'mobil' => '09998888888888',
            'address' => 'APlace',
            'sex' => '先生',
            'email' => 'bsotest@test.com'
        ));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame('BSO_TEST_CUSTOM', $responseArr['custom']['name'], $this->client->getResponse()->getContent());

        $crawler = $this->client->request('POST', "/api/v1/auction_custom/json", array(
            'name' => 'BSO_TEST_CUSTOM',
            'mobil' => '00000',
            'address' => 'APlace',
            'sex' => '先生',
            'email' => 'bsotest@test.com'
        ));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame(static::$kernel->getContainer()->get('translator')->trans('CustomMobilDuplicate'), $responseArr['msg'], $this->client->getResponse()->getContent());
    }

    public function testUpdateAction()
    {
        $crawler = $this->client->request('PUT', "/api/v1/auction_custom/1", array(
            'name' => 'BSO_TEST_CUSTOM',
            'mobil' => '00000',
            'address' => 'APlace',
            'sex' => '先生',
            'email' => 'bsotest@test.com'
        ));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame('BSO_TEST_CUSTOM', $responseArr['custom']['name'], $this->client->getResponse()->getContent());

        $crawler = $this->client->request('PUT', "/api/v1/auction_custom/999999999", array(
            'name' => 'BSO_TEST_CUSTOM',
            'mobil' => '09998888888888',
            'address' => 'APlace',
            'sex' => '先生',
            'email' => 'bsotest@test.com'
        ));
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame(static::$kernel->getContainer()->get('translator')->trans('CustomNotFound'), $responseArr['msg'], $this->client->getResponse()->getContent());
    }

    public function testDeleteAction()
    {
        $crawler = $this->client->request('DELETE', "/api/v1/auction_custom/5473");
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_SUCCESS, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame('test', $responseArr['custom']['name'], $this->client->getResponse()->getContent());

        $crawler = $this->client->request('DELETE', "/api/v1/auction_custom/1");
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame(static::$kernel->getContainer()->get('translator')->trans('CustomHasOrder'), $responseArr['msg'], $this->client->getResponse()->getContent());

        $crawler = $this->client->request('DELETE', "/api/v1/auction_custom/9999999");
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(Avenue::IS_ERROR, $responseArr['status'], $this->client->getResponse()->getContent());
        $this->assertSame(static::$kernel->getContainer()->get('translator')->trans('CustomNotFound'), $responseArr['msg'], $this->client->getResponse()->getContent());
    }
}

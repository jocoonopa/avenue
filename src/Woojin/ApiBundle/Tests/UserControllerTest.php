<?php

namespace Woojin\ApiBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use Woojin\Tests\Controller\AuthControllerTest;
use Woojin\UserBundle\Entity\Role;

/**
 * It's highly recommended that a functional test only tests the response
 */
class UserControllerTest extends AuthControllerTest
{
    public function testRoleListAction()
    {
        $crawler = $this->client->request('GET', "/api/v1/user/rolelist");
        $responseArr = json_decode($this->client->getResponse()->getContent(), true);

        $roles = Role::getRolelist();

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'), $this->client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        foreach ($roles as $roleName => $val) {
            $this->assertArrayHasKey($roleName, $responseArr);
        }
    }
}

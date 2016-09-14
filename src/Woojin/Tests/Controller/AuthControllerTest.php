<?php

namespace Woojin\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * It's highly recommended that a functional test only tests the response
 */
class AuthControllerTest extends WebTestCase
{
    protected $client;

    /**
     * You can access the static property $kernel to access the container
     */
    public function setUp()
    {
        static::bootKernel(array());

        $this->client = self::createClient(array(), array(
            'PHP_AUTH_USER' => static::$kernel->getContainer()->getParameter('test_username'),
            'PHP_AUTH_PW'   => static::$kernel->getContainer()->getParameter('test_password')
        ));
    }

    public function testForNothing(){}
}

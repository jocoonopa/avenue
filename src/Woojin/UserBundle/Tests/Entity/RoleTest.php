<?php
namespace Woojin\UserBundle\Tests\Entity;

use Woojin\UserBundle\Entity\Role;

class RoleTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRolelist()
    {
        $rolelist = Role::getRolelist();

        $this->assertTrue(is_array($rolelist));
        $this->assertSame(59, count($rolelist));
    }
}

<?php
namespace Woojin\Tests;

/**
 * This class doesn't mean anything to avenue,
 * it's just another playground test for my test practice
 */
class DependencyAndDataProviderComboTest extends \PHPUnit_Framework_TestCase
{
    public function provider()
    {
        return array(array('provider1'), array('provider1'));
    }

    public function testProducerFirst()
    {
        $this->assertTrue(true);
        return 'first';
    }

    public function testProducerSecond()
    {
        $this->assertTrue(true);
        return 'second';
    }

    /**
     * @dataProvider provider
     * @depends testProducerFirst
     * @depends testProducerSecond
     */
    public function testConsumer()
    {
        $this->assertEquals(
            array('provider1', 'first', 'second'),
            func_get_args()
        );
    }
}
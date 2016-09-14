<?php

namespace Woojin\Tests;

use Mockery as m;
use Woojin\Utility\Playground\Calculator;

/**
 * All those api methods in vendor/phpunit/phpunit/src/Framework/Assert.php
 */
class PlayGroundTest extends \PHPUnit_Framework_TestCase
{
    protected $myMock;

    /**
     * Invoked before each test
     */
    public function setUp()
    {        
        $this->myMock = m::mock('foo');
        $this->myMock2 = m::mock('car', array('foo' => 1,'bar' => 2));
        $this->myMock3 = m::mock('alias:MyStatic', array('foo' => 1));
        $this->myMock4 = m::mock('\Woojin\StoreBundle\Entity\Auction', array('getId' => 1));
        $this->myMock5 = m::mock('\Woojin\StoreBundle\Entity\Holiday[getId,getSchedule]', array('getId' => 0, 'getSchedule' => array()));
        $this->myMock6 = m::mock('\Woojin\StoreBundle\Entity\Holiday')->makePartial();
        $this->myMock7 = m::mock('\Woojin\StoreBundle\Entity\Holiday')->shouldIgnoreMissing();
        $this->myMock8 = m::mock('\Woojin\Utility\Playground\Calculator')->makePartial();
    }

    public function testMyMockClassName()
    {
        $this->assertSame('foo', get_parent_class($this->myMock));
        $this->assertSame('Mockery_0__foo', get_class($this->myMock));

        $this->myMock->shouldReceive('foo')->once()->andReturn(1);
        $this->assertSame(1, $this->myMock->foo());
    }

    public function testMyMock2ClassName()
    {
        $this->myMock2->shouldReceive('dog')->once()->andReturn(3);
        
        $this->assertSame('car', get_parent_class($this->myMock2));
        $this->assertSame('Mockery_1__car', get_class($this->myMock2));
        $this->assertEquals(1, $this->myMock2->foo());
        $this->assertEquals(2, $this->myMock2->bar());
        $this->assertEquals(3, $this->myMock2->dog());
    }

    public function testStatic()
    {
        $className = get_class($this->myMock3);
        $this->assertEquals(1, $className::foo());
    }

    public function testRealExtends()
    {
        $this->assertEquals(true, method_exists($this->myMock4, 'getId'));
        $this->assertEquals(1, $this->myMock4->getId());

        //************************************************************
        // Below will throw exception:
        // 
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        // BadMethodCallException: 
        // Received Mockery_2_Woojin_StoreBundle_Entity_Auction::getProduct(), 
        // but no expectations were specified
        // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        // 
        // The message means the method is exists there, but need to assign at least one
        // expectation 
        // 
        //============================================================
        //$this->assertEquals(NULL, $this->myMock4->getProduct());
        //************************************************************
    }

    public function testPartialMock()
    {
        $this->assertEquals(0, $this->myMock5->getId());
        $this->assertEquals(true, method_exists($this->myMock5, 'getUser'));
        $this->assertEquals(NULL, $this->myMock5->getUser());
        $this->assertSame('Woojin\StoreBundle\Entity\Holiday', $this->myMock5->getClassName());
    }

    public function testPassivePartialMock()
    {
        $this->assertSame('Woojin\StoreBundle\Entity\Holiday', $this->myMock6->getClassName());

        $this->myMock6->shouldReceive('getClassName')->once()->andReturn('foo');
        $this->assertSame('foo', $this->myMock6->getClassName());
    }

    /**
     * You could found that passthu call the real object method replace the mock one
     */
    public function testPassthru()
    {
        $this->myMock6->shouldReceive('getClassName')->once()->andReturn('foo');
        $this->assertSame('foo', $this->myMock6->getClassName());
        
        $this->myMock6->shouldReceive('getClassName')->once()->andReturn('foo')->passthru();
        $this->assertSame('Woojin\StoreBundle\Entity\Holiday', $this->myMock6->getClassName());
    }

    public function testEntityAttributes()
    {
        $this->myMock6->setSchedule(array('foo' => 0));
        $this->assertSame(array('foo' => 0), $this->myMock6->getSchedule());
    }

    public function testIgnoreMissing()
    {
        $this->assertSame(NULL, $this->myMock7->getClassName());
    }

    /**
     * Section 1 will override the org method, so the behavior would bee changed
     * Section 2 work as what we expected
     */
    public function testPassByReference()
    {
        /* Section1 */
        $num = 1;
        $this->myMock8->shouldReceive('plus')->once()->with(1)->andReturn(2);
        $this->assertSame(2, $this->myMock8->plus($num));
        $this->assertSame(1, $num);

        /* Section2 */
        $qq = 4;
        $this->assertSame(5, $this->myMock8->plus($qq));
        $this->assertSame(5, $qq);
    }

    public function testDemeterChain()
    {
        $this->myMock8->shouldReceive('a->b->c->getClassName')->andReturn('className');
        $this->assertSame('className', $this->myMock8->a(true)->b(true)->c()->getClassName());
        $this->assertSame(true, $this->myMock8 instanceof \Mockery\MockInterface);
    }

    /**
     * reference http://stackoverflow.com/questions/31219542/what-is-the-difference-between-overload-and-alias-in-mockery
     *
     * It will detect if the methods have generate the class we overload,
     * the overload mock will replace the real class
     */
    public function testOverride()
    {
        $mock = m::mock('overload:Woojin\Utility\Playground\MyClass');
        $mock->shouldreceive('someMethod')->once()->andReturn('someResult');

        $classToTest = new Calculator;
        $result = $classToTest->methodToTestOverride();

        $this->assertEquals('someResult', $result);
    }

    /**
     * reference http://stackoverflow.com/questions/31219542/what-is-the-difference-between-overload-and-alias-in-mockery
     *
     * It will detect if the methods we call have use what we alias, 
     * the alias will replace the real static method
     */
    public function testAlias()
    {
        $mock = m::mock('alias:\Woojin\Utility\Playground\MyClass');
        $mock->shouldreceive('someStaticMethod')->once()->andReturn('someResult');

        $classToTest = new Calculator;
        $result = $classToTest->methodToTestAlias();

        $this->assertEquals('someResult', $result);
    }

    public function testExpectOuput()
    {
        $this->expectOutputString('foo');
        print 'foo';
    }
    
    /**
     * Invoked after each test
     */
    public function tearDown()
    {
        m::close();
    }
}
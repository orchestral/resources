<?php namespace Orchestra\Resources\Tests;

use Mockery as m;
use Orchestra\Resources\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test constructing Orchestra\Resources\Container::route() method.
     *
     * @test
     */
    public function testConstructMethod()
    {
        $stub = new Container('foo', 'FooController');

        $refl       = new \ReflectionObject($stub);
        $attributes = $refl->getProperty('attributes');
        $attributes->setAccessible(true);

        $expected = array(
            'name'    => 'Foo',
            'id'      => 'foo',
            'childs'  => array(),
            'uses'    => 'FooController',
            'visible' => true,
        );

        $this->assertEquals($expected, $attributes->getValue($stub));
        $this->assertEquals('FooController', $stub->uses());
    }

    /**
     * Test Orchestra\Resources\Container visibility methods.
     *
     * @test
     */
    public function testVisibilityMethods()
    {
        $stub = new Container('foo', 'FooController');

        $this->assertTrue($stub->visible);

        $this->assertEquals($stub, $stub->hide());
        $this->assertFalse($stub->visible);

        $this->assertEquals($stub, $stub->show());
        $this->assertTrue($stub->visible);
    }

    /**
     * Test Orchestra\Resources\Container::visibility() method
     * throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testVisibilityMethodThrowsException()
    {
        with(new Container('foo', 'FooController'))->visibility('foo');
    }

    /**
     * Test Orchestra\Resources\Container routing methods.
     *
     * @test
     */
    public function testRoutingMethods()
    {
        $stub = new Container('foo', 'FooController');

        $this->assertEquals($stub, $stub->route('first', 'FirstController'));

        $stub->second = 'SecondController';
        $stub['third'] = 'ThirdController';

        $this->assertEquals('SecondController', $stub['second']);
        $this->assertTrue(isset($stub['second']));
        $this->assertFalse(isset($stub['ten']));
        unset($stub['first']);
        $this->assertFalse(isset($stub['first']));
    }

    /**
     * Test Orchestra\Resources\Container::route() method given reserved
     * name throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testRouteMethodGivenReservedNameThrowsException()
    {
        with(new Container('foo', 'FooController'))->route('visible', 'FirstController');
    }

    /**
     * Test Orchestra\Resources\Container::route() method given name with
     * "/" throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testRouteMethodGivenNameWithSlashesThrowsException()
    {
        with(new Container('foo', 'FooController'))->route('first/foo', 'FirstController');
    }

    /**
     * Test Orchestra\Resources\Container::__call() method
     * throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMagicMethodCallThrowsException()
    {
        with(new Container('foo', 'FooController'))->uses('FoobarController');
    }
}

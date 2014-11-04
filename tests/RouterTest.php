<?php namespace Orchestra\Resources\TestCase;

use Mockery as m;
use Orchestra\Resources\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test constructing Orchestra\Resources\Router::route() method.
     *
     * @test
     */
    public function testConstructMethod()
    {
        $stub = new Router('foo', 'FooController');

        $refl       = new \ReflectionObject($stub);
        $attributes = $refl->getProperty('attributes');
        $attributes->setAccessible(true);

        $expected = array(
            'name'    => 'Foo',
            'id'      => 'foo',
            'routes'  => array(),
            'uses'    => 'FooController',
            'visible' => true,
        );

        $this->assertEquals($expected, $attributes->getValue($stub));
        $this->assertEquals('FooController', $stub->uses());

        $stub->set('foo', 'foobar');

        $this->assertEquals('foobar', $stub->get('foo'));

        $stub->forget('foo');

        $this->assertNull($stub->get('foo'));
    }

    /**
     * Test Orchestra\Resources\Router visibility methods.
     *
     * @test
     */
    public function testVisibilityMethods()
    {
        $stub = new Router('foo', 'FooController');

        $this->assertTrue($stub->visible);

        $this->assertEquals($stub, $stub->hide());
        $this->assertFalse($stub->visible);

        $this->assertEquals($stub, $stub->show());
        $this->assertTrue($stub->visible);
    }

    /**
     * Test Orchestra\Resources\Router::visibility() method
     * throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testVisibilityMethodThrowsException()
    {
        with(new Router('foo', 'FooController'))->visibility('foo');
    }

    /**
     * Test Orchestra\Resources\Router routing methods.
     *
     * @test
     */
    public function testRoutingMethods()
    {
        $stub = new Router('foo', 'FooController');

        $this->assertEquals($stub, $stub->route('first', 'FirstController'));

        $stub->second = 'SecondController';
        $stub['third'] = 'ThirdController';
        $stub['third.fourth'] = 'ForthController';

        $expected = array(
            'first' => 'FirstController',
            'second' => 'SecondController',
            'third' => 'ThirdController',
            'third.fourth' => 'ForthController',
        );

        $this->assertEquals($expected, $stub->get('routes'));

        unset($stub['first']);

        $this->assertEquals('ForthController', $stub['third.fourth']);
        $this->assertFalse(isset($stub['first']));
    }

    /**
     * Test Orchestra\Resources\Router::route() method given reserved
     * name throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testRouteMethodGivenReservedNameThrowsException()
    {
        with(new Router('foo', 'FooController'))->route('visible', 'FirstController');
    }

    /**
     * Test Orchestra\Resources\Router::route() method given name with
     * "/" throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testRouteMethodGivenNameWithSlashesThrowsException()
    {
        with(new Router('foo', 'FooController'))->route('first/foo', 'FirstController');
    }

    /**
     * Test Orchestra\Resources\Router::__call() method
     * throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMagicMethodCallThrowsException()
    {
        with(new Router('foo', 'FooController'))->uses('FoobarController');
    }
}

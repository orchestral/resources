<?php namespace Orchestra\Resources\TestCase;

use Mockery as m;
use Orchestra\Resources\Container;
use Orchestra\Resources\Factory;
use Orchestra\Resources\Response;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Dispatcher instance.
     *
     * @var Orchestra\Resources\Dispatcher
     */
    private $dispatcher = null;

    /**
     * Response instance.
     *
     * @var Orchestra\Resources\Response
     */
    private $response = null;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->dispatcher = m::mock('\Orchestra\Resources\Dispatcher');
        $this->response   = m::mock('\Orchestra\Resources\Response');
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        unset($this->dispatcher);
        unset($this->response);
        m::close();
    }

    /**
     * Test Orchestra\Resources\Factory::make() method.
     *
     * @test
     */
    public function testMakeMethod()
    {
        $stub = new Factory($this->dispatcher, $this->response);

        $stub->make('foo', 'FooController');

        $refl    = new \ReflectionObject($stub);
        $drivers = $refl->getProperty('drivers');
        $drivers->setAccessible(true);

        $output = $drivers->getValue($stub);

        $this->assertInstanceOf('\Orchestra\Resources\Container', $output['foo']);
    }

    /**
     * Test Orchestra\Resources\Factory::make() method given name with
     * "." throw exceptions.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMakeMethodGivenNameWithDottedThrowsException()
    {
        $stub = new Factory($this->dispatcher, $this->response);

        $stub->make('foo.bar', 'FooController');
    }

    /**
     * Test Orchestra\Resources\Factory::make() method given name with
     * "/" throw exceptions.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMakeMethodGivenNameWithSlashesThrowsException()
    {
        $stub = new Factory($this->dispatcher, $this->response);

        $stub->make('foo/bar', 'FooController');
    }

    /**
     * Test Orchestra\Resources\Factory::make() method.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMakeMethodThrowsException()
    {
        $stub = new Factory($this->dispatcher, $this->response);

        $stub->make('foo', null);
    }

    /**
     * Test Orchestra\Resources\Factory::of() method.
     *
     * @test
     */
    public function testOfMethod()
    {
        $stub = new Factory($this->dispatcher, $this->response);
        $stub->of('foobar', 'FoobarController');

        $refl    = new \ReflectionObject($stub);
        $drivers = $refl->getProperty('drivers');
        $drivers->setAccessible(true);

        $output = $drivers->getValue($stub);

        $this->assertInstanceOf('\Orchestra\Resources\Container', $output['foobar']);
        $this->assertEquals($output['foobar'], $stub->of('foobar'));
    }

    /**
     * Test Orchestra\Resources\Factory::call() method.
     *
     * @test
     */
    public function testCallMethod()
    {
        $dispatcher = $this->dispatcher;
        $stub = new Factory($dispatcher, $this->response);

        $mock = array(
            'foo'    => new Container('Foo', 'FooController'),
            'foobar' => new Container('Foobar', 'FoobarController'),
        );

        $refl    = new \ReflectionObject($stub);
        $drivers = $refl->getProperty('drivers');
        $drivers->setAccessible(true);

        $drivers->setValue($stub, $mock);

        $dispatcher->shouldReceive('call')->with($mock['foo'], 'foobar', array())->once()->andReturn('FOO');
        $dispatcher->shouldReceive('call')->with($mock['foobar'], null, array())->once()->andReturn('FOOBAR');

        $this->assertEquals('FOO', $stub->call('foo.foobar', array()));
        $this->assertEquals('FOOBAR', $stub->call('foobar', array()));
        $this->assertFalse($stub->call('foo-not-available', array()));
    }

    /**
     * Test Orchestra\Resources\Factory::response() method.
     *
     * @test
     */
    public function testResponseMethod()
    {
        $response = $this->response;
        $stub = new Factory($this->dispatcher, $response);

        $callback = function () { return ''; };
        $response->shouldReceive('call')->with('foo', $callback)->once()->andReturn(true);

        $this->assertTrue($stub->response('foo', $callback));
    }

    /**
     * Test Orchestra\Resources\Factory::all() method.
     *
     * @test
     */
    public function testAllMethod()
    {
        $stub = new Factory($this->dispatcher, $this->response);

        $refl    = new \ReflectionObject($stub);
        $drivers = $refl->getProperty('drivers');
        $drivers->setAccessible(true);

        $expected = array(
            'foo'    => 'Foo',
            'foobar' => 'Foobar',
        );

        $drivers->setValue($stub, $expected);

        $this->assertEquals($expected, $stub->all());
    }
}
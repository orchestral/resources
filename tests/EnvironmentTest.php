<?php namespace Orchestra\Resources\Tests;

use Mockery as m;
use Orchestra\Resources\Environment;
use Orchestra\Resources\Response;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Application instance.
     *
     * @var Illuminate\Foundation\Application
     */
    private $app = null;

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
        $this->app        = array();
        $this->dispatcher = m::mock('\Orchestra\Resources\Dispatcher');
        $this->response   = m::mock('\Orchestra\Resources\Response');
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        unset($this->app);
        unset($this->dispatcher);
        unset($this->response);
        m::close();
    }

    /**
     * Test Orchestra\Resources\Environment::make() method.
     *
     * @test
     */
    public function testMakeMethod()
    {
        $stub = new Environment($this->app, $this->dispatcher, $this->response);

        $stub->make('foo', 'FooController');

        $refl    = new \ReflectionObject($stub);
        $drivers = $refl->getProperty('drivers');
        $drivers->setAccessible(true);

        $output = $drivers->getValue($stub);

        $this->assertInstanceOf('\Orchestra\Resources\Container', $output['foo']);
    }

    /**
     * Test Orchestra\Resources\Environment::make() method given name with
     * "." throw exceptions.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMakeMethodGivenNameWithDottedThrowsException()
    {
        $stub = new Environment($this->app, $this->dispatcher, $this->response);

        $stub->make('foo.bar', 'FooController');
    }

    /**
     * Test Orchestra\Resources\Environment::make() method given name with
     * "/" throw exceptions.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMakeMethodGivenNameWithSlashesThrowsException()
    {
        $stub = new Environment($this->app, $this->dispatcher, $this->response);

        $stub->make('foo/bar', 'FooController');
    }

    /**
     * Test Orchestra\Resources\Environment::make() method.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testMakeMethodThrowsException()
    {
        $stub = new Environment($this->app, $this->dispatcher, $this->response);

        $stub->make('foo', null);
    }

    /**
     * Test Orchestra\Resources\Environment::of() method.
     *
     * @test
     */
    public function testOfMethod()
    {
        $stub = new Environment($this->app, $this->dispatcher, $this->response);
        $stub->of('foobar', 'FoobarController');

        $refl    = new \ReflectionObject($stub);
        $drivers = $refl->getProperty('drivers');
        $drivers->setAccessible(true);

        $output = $drivers->getValue($stub);

        $this->assertInstanceOf('\Orchestra\Resources\Container', $output['foobar']);
        $this->assertEquals($output['foobar'], $stub->of('foobar'));
    }

    /**
     * Test Orchestra\Resources\Environment::call() method.
     *
     * @test
     */
    public function testCallMethod()
    {
        $dispatcher = $this->dispatcher;
        $stub = new Environment($this->app, $dispatcher, $this->response);

        $refl    = new \ReflectionObject($stub);
        $drivers = $refl->getProperty('drivers');
        $drivers->setAccessible(true);

        $drivers->setValue($stub, array(
            'foo'    => 'Foo',
            'foobar' => 'Foobar',
        ));

        $dispatcher->shouldReceive('call')->with('Foo', 'foobar', array())->once()->andReturn('FOO');
        $dispatcher->shouldReceive('call')->with('Foobar', null, array())->once()->andReturn('FOOBAR');

        $this->assertEquals('FOO', $stub->call('foo.foobar', array()));
        $this->assertEquals('FOOBAR', $stub->call('foobar', array()));
    }

    /**
     * Test Orchestra\Resources\Environment::response() method.
     *
     * @test
     */
    public function testResponseMethod()
    {
        $response = $this->response;
        $stub = new Environment($this->app, $this->dispatcher, $response);

        $callback = function () { return ''; };
        $response->shouldReceive('call')->with('foo', $callback)->once()->andReturn(true);

        $this->assertTrue($stub->response('foo', $callback));
    }

    /**
     * Test Orchestra\Resources\Environment::all() method.
     *
     * @test
     */
    public function testAllMethod()
    {
        $stub = new Environment($this->app, $this->dispatcher, $this->response);

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

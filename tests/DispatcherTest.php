<?php namespace Orchestra\Resources\TestCase;

use Mockery as m;
use Illuminate\Routing\Controller;
use Orchestra\Resources\Container;
use Orchestra\Resources\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Container\Container
     */
    private $app = null;

    /**
     * Router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    private $router = null;

    /**
     * Request instance.
     *
     * @var \Illuminate\Http\Request
     */
    private $request = null;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->app     = m::mock('\Illuminate\Container\Container');
        $this->router  = m::mock('\Illuminate\Routing\Router');
        $this->request = m::mock('\Illuminate\Http\Request');
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        unset($this->app);
        unset($this->router);
        unset($this->request);
        m::close();
    }

    /**
     * Test Orchestra\Resources\Dispatcher::call() method using GET verb.
     *
     * @test
     */
    public function testCallMethodUsingGetVerb()
    {
        $app        = $this->app;
        $router     = $this->router;
        $request    = $this->request;
        $useApp     = new AppController;
        $useFoo     = new FooController;
        $useFoobar  = new FoobarController;

        $app->shouldReceive('make')->with('AppController')->once()->andReturn($useApp)
            ->shouldReceive('make')->with('FooController')->once()->andReturn($useFoo)
            ->shouldReceive('make')->times(3)->with('FoobarController')->andReturn($useFoobar);
        $request->shouldReceive('getMethod')->times(6)->andReturn('GET');
        $router->shouldReceive('getMiddleware')->times(5)->andReturn([]);

        $driver = new Container('app', array(
            'name'   => 'app',
            'uses'   => 'AppController',
            'routes' => array(
                'foo' => 'restful:FooController',
                'foo.bar' => 'resource:FoobarController',
            ),
        ));

        $stub = new Dispatcher($app, $router, $request);

        $this->assertEquals('AppController@getIndex', $stub->call($driver, null, array('index')));
        $this->assertEquals('FooController@getEdit', $stub->call($driver, 'foo', array('edit')));
        $this->assertEquals('FoobarController@edit', $stub->call($driver, 'foo', array(1, 'bar', 2, 'edit')));
        $this->assertEquals('FoobarController@index', $stub->call($driver, 'foo', array(1, 'bar')));
        $this->assertEquals('FoobarController@show', $stub->call($driver, 'foo', array(1, 'bar', 2)));
        $this->assertFalse($stub->call($driver, 'not-available'));
    }

    /**
     * Test Orchestra\Resources\Dispatcher::call() method using POST verb.
     *
     * @test
     */
    public function testCallMethodUsingPostVerb()
    {
        $app       = $this->app;
        $router    = $this->router;
        $request   = $this->request;
        $useFoobar = new FoobarController;

        $app->shouldReceive('make')->once()->with('FoobarController')->andReturn($useFoobar);
        $request->shouldReceive('getMethod')->once()->andReturn('POST');
        $router->shouldReceive('getMiddleware')->once()->andReturn([]);

        $driver = new Container('app', array(
            'name'   => 'app',
            'uses'   => 'AppController',
            'routes' => array(
                'foo' => 'restful:FooController',
                'foo.bar' => 'resource:FoobarController',
            ),
        ));

        $stub = new Dispatcher($app, $router, $request);

        $this->assertEquals('FoobarController@store', $stub->call($driver, 'foo', array(1, 'bar', 2)));
    }

    /**
     * Test Orchestra\Resources\Dispatcher::call() method using PUT verb.
     *
     * @test
     */
    public function testCallMethodUsingPutVerb()
    {
        $app       = $this->app;
        $router    = $this->router;
        $request   = $this->request;
        $useFoobar = new FoobarController;

        $app->shouldReceive('make')->once()->with('FoobarController')->andReturn($useFoobar);
        $request->shouldReceive('getMethod')->once()->andReturn('PUT');
        $router->shouldReceive('getMiddleware')->once()->andReturn([]);

        $driver = new Container('app', array(
            'name'   => 'app',
            'uses'   => 'AppController',
            'routes' => array(
                'foo' => 'restful:FooController',
                'foo.bar' => 'resource:FoobarController',
            ),
        ));

        $stub = new Dispatcher($app, $router, $request);

        $this->assertEquals('FoobarController@update', $stub->call($driver, 'foo', array(1, 'bar', 2)));
    }

    /**
     * Test Orchestra\Resources\Dispatcher::call() method using GET verb.
     *
     * @test
     */
    public function testCallMethodUsingDeleteVerb()
    {
        $app       = $this->app;
        $router    = $this->router;
        $request   = $this->request;
        $useFoobar = new FoobarController;

        $app->shouldReceive('make')->once()->with('FoobarController')->andReturn($useFoobar);
        $request->shouldReceive('getMethod')->once()->andReturn('DELETE');
        $router->shouldReceive('getMiddleware')->once()->andReturn([]);

        $driver = new Container('app', array(
            'name'   => 'app',
            'uses'   => 'AppController',
            'routes' => array(
                'foo' => 'restful:FooController',
                'foo.bar' => 'resource:FoobarController',
            ),
        ));

        $stub = new Dispatcher($app, $router, $request);

        $this->assertEquals('FoobarController@destroy', $stub->call($driver, 'foo', array(1, 'bar', 2)));
    }

    /**
     * Test Orchestra\Resources\Dispatcher::call() method throws exception.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testCallMethodThrowsException()
    {
        $request = $this->request;
        $router  = $this->router;
        $stub    = new Dispatcher($this->app, $router, $request);

        $request->shouldReceive('getMethod')->once()->andReturn('GET');

        $driver = new Container('app', array(
            'name'   => 'app',
            'uses'   => 'request:AppController',
            'childs' => array(),
        ));

        $stub->call($driver, null, array('edit'));
    }
}

class AppController extends Controller
{
    public function getIndex()
    {
        return 'AppController@getIndex';
    }
}

class FooController extends Controller
{
    public function getEdit()
    {
        return 'FooController@getEdit';
    }
}

class FoobarController extends Controller
{
    public function index()
    {
        return 'FoobarController@index';
    }

    public function show()
    {
        return 'FoobarController@show';
    }

    public function store()
    {
        return 'FoobarController@store';
    }

    public function edit()
    {
        return 'FoobarController@edit';
    }

    public function update()
    {
        return 'FoobarController@update';
    }

    public function destroy()
    {
        return 'FoobarController@destroy';
    }
}

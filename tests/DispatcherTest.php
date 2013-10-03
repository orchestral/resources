<?php namespace Orchestra\Resources\Tests;

use Mockery as m;
use Orchestra\Resources\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Application instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	private $app = null;

	/**
	 * Router instance.
	 *
	 * @var Illuminate\Routing\Router
	 */
	private $router = null;

	/**
	 * Request instance.
	 *
	 * @var Illuminate\Http\Request
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
		$useApp     = m::mock('AppController');
		$useFoo     = m::mock('FooController');
		$useFoobar  = m::mock('FoobarController');
		
		$app->shouldReceive('make')->with('AppController')->once()->andReturn($useApp)
			->shouldReceive('make')->with('FooController')->once()->andReturn($useFoo)
			->shouldReceive('make')->times(3)->with('FoobarController')->andReturn($useFoobar);
		$useApp->shouldReceive('getBeforeFilters')->once()->andReturn(array())
			->shouldReceive('getAfterFilters')->once()->andReturn(array())
			->shouldReceive('getIndex')->once()->andReturn('AppController@getIndex');
		$useFoo->shouldReceive('getBeforeFilters')->once()->andReturn(array())
			->shouldReceive('getAfterFilters')->once()->andReturn(array())
			->shouldReceive('getEdit')->once()->andReturn('FooController@getEdit');
		$useFoobar->shouldReceive('getBeforeFilters')->times(3)->andReturn(array())
			->shouldReceive('getAfterFilters')->times(3)->andReturn(array())
			->shouldReceive('index')->once()->andReturn('FoobarController@index')
			->shouldReceive('show')->once()->andReturn('FoobarController@show')
			->shouldReceive('edit')->once()->andReturn('FoobarController@edit');
		$request->shouldReceive('getMethod')->times(5)->andReturn('GET');

		$driver = (object) array(
			'id'     => 'app',
			'uses'   => 'AppController',
			'childs' => array(
				'foo' => 'restful:FooController',
				'foo.bar' => 'resource:FoobarController',
			),
		);

		$stub = new Dispatcher($this->app, $router, $request);
		
		$this->assertEquals('AppController@getIndex', $stub->call($driver, null, array('index')));
		$this->assertEquals('FooController@getEdit', $stub->call($driver, 'foo', array('edit')));
		$this->assertEquals('FoobarController@edit', $stub->call($driver, 'foo', array(1, 'bar', 2, 'edit')));
		$this->assertEquals('FoobarController@index', $stub->call($driver, 'foo', array(1, 'bar')));
		$this->assertEquals('FoobarController@show', $stub->call($driver, 'foo', array(1, 'bar', 2)));
	}

	/**
	 * Test Orchestra\Resources\Dispatcher::call() method using POST verb.
	 *
	 * @test
	 */
	public function testCallMethodUsingPostVerb()
	{
		$app       = $this->app;
		$request   = $this->request;
		$useFoobar = m::mock('FoobarController');

		$app->shouldReceive('make')->once()->with('FoobarController')->andReturn($useFoobar);
		$useFoobar->shouldReceive('getBeforeFilters')->once()->andReturn(array())
			->shouldReceive('getAfterFilters')->once()->andReturn(array())
			->shouldReceive('store')->once()->andReturn('FoobarController@store');
		$request->shouldReceive('getMethod')->once()->andReturn('POST');

		$driver = (object) array(
			'id'     => 'app',
			'uses'   => 'AppController',
			'childs' => array(
				'foo' => 'restful:FooController',
				'foo.bar' => 'resource:FoobarController',
			),
		);
		$stub = new Dispatcher($this->app, $this->router, $request);
		
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
		$request   = $this->request;
		$useFoobar = m::mock('FoobarController');

		$app->shouldReceive('make')->once()->with('FoobarController')->andReturn($useFoobar);
		$useFoobar->shouldReceive('getBeforeFilters')->once()->andReturn(array())
			->shouldReceive('getAfterFilters')->once()->andReturn(array())
			->shouldReceive('update')->once()->andReturn('FoobarController@update');
		$request->shouldReceive('getMethod')->once()->andReturn('PUT');

		$driver = (object) array(
			'id'     => 'app',
			'uses'   => 'AppController',
			'childs' => array(
				'foo' => 'restful:FooController',
				'foo.bar' => 'resource:FoobarController',
			),
		);

		$stub = new Dispatcher($app, $this->router, $request);

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
		$request   = $this->request;
		$useFoobar = m::mock('FoobarController');

		$app->shouldReceive('make')->once()->with('FoobarController')->andReturn($useFoobar);
		$useFoobar->shouldReceive('getBeforeFilters')->once()->andReturn(array())
			->shouldReceive('getAfterFilters')->once()->andReturn(array())
			->shouldReceive('destroy')->once()->andReturn('FoobarController@destroy');
		$request->shouldReceive('getMethod')->once()->andReturn('DELETE');

		$driver = (object) array(
			'id'     => 'app',
			'uses'   => 'AppController',
			'childs' => array(
				'foo' => 'restful:FooController',
				'foo.bar' => 'resource:FoobarController',
			),
		);

		$stub = new Dispatcher($app, $this->router, $request);

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
		$stub    = new Dispatcher($this->app, $this->router, $request);

		$request->shouldReceive('getMethod')->once()->andReturn('GET');

		$driver = (object) array(
			'uses'   => 'request:AppController',
			'childs' => array(),
		);

		$stub->call($driver, null, array('edit'));
	}
}

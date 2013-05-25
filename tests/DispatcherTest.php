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
		$this->app     = m::mock('\Illuminate\Foundation\Application');
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
	 * Test Orchestra\Resources\Dispatcher::call() method.
	 *
	 * @test
	 */
	public function testCallMethod()
	{
		$app = $this->app;
		
		$requestGet    = $this->request;
		$stubGet       = new Dispatcher($this->app, $this->router, $requestGet);
		$requestPost   = $this->request;
		$stubPost      = new Dispatcher($this->app, $this->router, $requestPost);
		$requestPut    = $this->request;
		$stubPut       = new Dispatcher($this->app, $this->router, $requestPut);
		$requestDelete = $this->request;
		$stubDelete    = new Dispatcher($this->app, $this->router, $requestDelete);

		$requestGet->shouldReceive('getMethod')->times(5)->andReturn('GET');
		$requestPost->shouldReceive('getMethod')->times(1)->andReturn('POST');
		$requestPut->shouldReceive('getMethod')->times(1)->andReturn('PUT');
		$requestDelete->shouldReceive('getMethod')->times(1)->andReturn('DELETE');

		$useApp    = m::mock('AppController');
		$useFoo    = m::mock('FooController');
		$useFoobar = m::mock('FoobarController');

		$app->shouldReceive('make')->with('AppController')->once()->andReturn($useApp)
			->shouldReceive('make')->with('FooController')->once()->andReturn($useFoo)
			->shouldReceive('make')->with('FoobarController')->times(6)->andReturn($useFoobar);
		$useApp->shouldReceive('callAction')->once()->andReturn('useApp');
		$useFoo->shouldReceive('callAction')->once()->andReturn('useFoo');
		$useFoobar->shouldReceive('callAction')->times(6)->andReturn('useFoobar');

		$driver = (object) array(
			'uses'   => 'AppController',
			'childs' => array(
				'foo' => 'restful:FooController',
				'foo.bar' => 'resource:FoobarController',
			),
		);

		$this->assertEquals('useApp', $stubGet->call($driver, null, array('edit')));
		$this->assertEquals('useFoo', $stubGet->call($driver, 'foo', array('edit')));
		$this->assertEquals('useFoobar', $stubGet->call($driver, 'foo', array(1, 'bar', 2, 'edit')));
		$this->assertEquals('useFoobar', $stubGet->call($driver, 'foo', array(1, 'bar')));
		$this->assertEquals('useFoobar', $stubGet->call($driver, 'foo', array(1, 'bar', 2)));
		$this->assertEquals('useFoobar', $stubPost->call($driver, 'foo', array(1, 'bar', 2)));
		$this->assertEquals('useFoobar', $stubPut->call($driver, 'foo', array(1, 'bar', 2)));
		$this->assertEquals('useFoobar', $stubDelete->call($driver, 'foo', array(1, 'bar', 2)));
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

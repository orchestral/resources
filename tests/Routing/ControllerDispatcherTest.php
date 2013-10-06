<?php namespace Orchestra\Resources\Routing\TestCase;

use Mockery as m;
use Orchestra\Resources\Routing\ControllerDispatcher;

class ControllerDispatcherTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		m::close();
	}

	/**
	 * Test Orchestra\Resources\Routing\ControllerDispatcher::run() method.
	 *
	 * @test
	 */
	public function testRunMethod()
	{
		$container = m::mock('\Illuminate\Container\Container');
		$router    = m::mock('\Illuminate\Routing\RouteFiltererInterface');
		$route     = m::mock('\Illuminate\Routing\Route');
		$request   = m::mock('\Illuminate\Http\Request');
		$useFoo    = new FooController; 

		$container->shouldReceive('make')->once()->with('FooController')->andReturn($useFoo);
		$route->shouldReceive('parametersWithoutNulls')->once()->andReturn(array());

		$stub = new ControllerDispatcher($router, $container);

		$this->assertInstanceOf('\Illuminate\Routing\ControllerDispatcher', $stub);
		$this->assertEquals('FooController@getIndex', $stub->run('FooController', 'getIndex', $route, $request));
	}

	/**
	 * Test Orchestra\Resources\Routing\ControllerDispatcher::run() method.
	 *
	 * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function testRunMethodThrowsException()
	{
		$container = m::mock('\Illuminate\Container\Container');
		$router    = m::mock('\Illuminate\Routing\RouteFiltererInterface');
		$route     = m::mock('\Illuminate\Routing\Route');
		$request   = m::mock('\Illuminate\Http\Request');
		$useFoo    = new FooController; 

		$container->shouldReceive('make')->once()->with('FooController')->andReturn($useFoo);
		$route->shouldReceive('parametersWithoutNulls')->once()->andReturn(array());

		$stub = new ControllerDispatcher($router, $container);

		$this->assertInstanceOf('\Illuminate\Routing\ControllerDispatcher', $stub);
		$stub->run('FooController', 'getMissingMethod', $route, $request);
	}
}

class FooController extends \Illuminate\Routing\Controller {

	public function getIndex()
	{
		return 'FooController@getIndex';
	}
}

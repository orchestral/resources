<?php namespace Orchestra\Resources\Routing\TestCase;

use Mockery as m;
use Illuminate\Routing\Controller;
use Orchestra\Resources\Routing\ControllerDispatcher;

class ControllerDispatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Resources\Routing\ControllerDispatcher::call() method
     * when method doesn't exist.
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testRunMethodThrowsException()
    {
        $container = m::mock('\Illuminate\Container\Container');
        $router    = m::mock('\Illuminate\Routing\Router');
        $route     = m::mock('\Illuminate\Routing\Route');
        $request   = m::mock('\Illuminate\Http\Request');
        $useFoo    = new FooController();

        $router->shouldReceive('getMiddleware')->once()->andReturn([]);

        $container->shouldReceive('make')->once()->with('FooController')->andReturn($useFoo);

        $stub = new ControllerDispatcher($router, $container);

        $stub->dispatch($route, $request, 'FooController', 'getMissingMethod');
    }
}

class FooController extends Controller
{
    public function getIndex()
    {
        return 'FooController@getIndex';
    }
}

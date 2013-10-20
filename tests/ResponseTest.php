<?php namespace Orchestra\Resources\Tests;

use Mockery as m;
use Orchestra\Resources\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Application instance.
     *
     * @var Illuminate\Foundation\Application
     */
    private $app = null;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->app = m::mock('\Illuminate\Foundation\Application');
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        unset($this->app);
        m::close();
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given empty
     * string.
     *
     * @test
     */
    public function testCallMethodWhenGivenEmptyString()
    {
        $app  = $this->app;
        $stub = new Response($app);

        $this->assertEquals('', $stub->call(''));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given null.
     *
     * @test
     */
    public function testCallMethodWhenGivenNull()
    {
        $app  = $this->app;
        $stub = new Response($app);

        $app->shouldReceive('abort')->once()->with(404)->andReturn('404 foo');
        $this->assertEquals('404 foo', $stub->call(null));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given false.
     *
     * @test
     */
    public function testCallMethodWhenGivenFalse()
    {
        $app  = $this->app;
        $stub = new Response($app);

        $app->shouldReceive('abort')->once()->with(404)->andReturn('404 foo');
        $this->assertEquals('404 foo', $stub->call(false));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given
     * Illuminate\Http\RedirectResponse.
     *
     * @test
     */
    public function testCallMethodWhenGivenRedirectResponse()
    {
        $stub = new Response($this->app);

        $content = m::mock('\Illuminate\Http\RedirectResponse');
        $this->assertEquals($content, $stub->call($content));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given
     * Illuminate\Http\JsonResponse.
     *
     * @test
     */
    public function testCallMethodWhenGivenJsonResponse()
    {
        $stub = new Response($this->app);

        $content = m::mock('\Illuminate\Http\JsonResponse');
        $this->assertEquals($content, $stub->call($content));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given
     * Orchestra\Facile\Response.
     *
     * @test
     */
    public function testCallMethodWhenGivenFacileResponse()
    {
        $stub = new Response($this->app);

        $content = m::mock('\Orchestra\Facile\Response');
        $content->shouldReceive('render')->once()->andReturn('foo');
        $this->assertEquals('foo', $stub->call($content));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given
     * Illuminate\Http\Response.
     *
     * @test
     */
    public function testCallMethodWhenGivenIlluminateResponse()
    {
        $app  = $this->app;
        $stub = new Response($app);

        $app->shouldReceive('abort')->once()->with(404)->andReturn('404 foo');

        $callback = function ($content) {
            return "<strong>{$content}</strong>";
        };

        $content = m::mock('\Illuminate\Http\Response');
        $content->headers = $headers = m::mock('HeaderBag');
        $content->shouldReceive('getStatusCode')->once()->andReturn(200)
            ->shouldReceive('getContent')->once()->andReturn('foo')
            ->shouldReceive('isSuccessful')->once()->andReturn(true);
        $headers->shouldReceive('get')->with('Content-Type')->once()->andReturn('text/html');
        $this->assertEquals('<strong>foo</strong>', $stub->call($content, $callback));

        $content = m::mock('\Illuminate\Http\Response');
        $content->headers = $headers = m::mock('HeaderBag');
        $facile = m::mock('\Orchestra\Facile\Response');
        $facile->shouldReceive('getFormat')->andReturn('json')
            ->shouldReceive('render')->once()->andReturn('foo');
        $content->shouldReceive('getStatusCode')->once()->andReturn(200)
            ->shouldReceive('getContent')->once()->andReturn($facile)
            ->shouldReceive('isSuccessful')->never()->andReturn(true);
        $headers->shouldReceive('get')->with('Content-Type')->once()->andReturn('text/json');
        $this->assertEquals('foo', $stub->call($content));

        $content = m::mock('\Illuminate\Http\Response');
        $content->headers = $headers = m::mock('HeaderBag');
        $content->shouldReceive('getStatusCode')->once()->andReturn(200)
            ->shouldReceive('getContent')->once()->andReturn('foo')
            ->shouldReceive('isSuccessful')->never()->andReturn(true);
        $headers->shouldReceive('get')->with('Content-Type')->once()->andReturn('application/json');
        $this->assertEquals($content, $stub->call($content));

        $content = m::mock('\Illuminate\Http\Response');
        $content->headers = $headers = m::mock('HeaderBag');
        $content->shouldReceive('getStatusCode')->once()->andReturn(404)
            ->shouldReceive('getContent')->once()->andReturn('foo')
            ->shouldReceive('isSuccessful')->once()->andReturn(false);
        $headers->shouldReceive('get')->with('Content-Type')->once()->andReturn('text/html');
        $this->assertEquals('404 foo', $stub->call($content));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given string.
     *
     * @test
     */
    public function testCallMethodWhenGivenString()
    {
        $stub = new Response($this->app);
        $this->assertEquals('Foo', $stub->call('Foo'));
    }
}

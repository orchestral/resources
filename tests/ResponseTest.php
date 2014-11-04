<?php namespace Orchestra\Resources\TestCase;

use Mockery as m;
use Orchestra\Resources\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
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
        $stub = new Response;

        $this->assertEquals('', $stub->call(''));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given null.
     *
     * @test
     */
    public function testCallMethodWhenGivenNull()
    {
        $stub = new Response;
        $response = $stub->call(null);

        $this->assertInstanceOf('\Illuminate\Http\Response', $response);
        $this->assertEquals('', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given false.
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testCallMethodWhenGivenFalse()
    {
        $stub = new Response;
        $stub->call(false);
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given
     * Illuminate\Http\RedirectResponse.
     *
     * @test
     */
    public function testCallMethodWhenGivenRedirectResponse()
    {
        $stub = new Response;

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
        $stub = new Response;

        $content = m::mock('\Illuminate\Http\JsonResponse');
        $this->assertEquals($content, $stub->call($content));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given
     * Orchestra\Facile\Facile.
     *
     * @test
     */
    public function testCallMethodWhenGivenFacileResponse()
    {
        $stub = new Response;

        $content = m::mock('\Orchestra\Facile\Facile');
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
        $stub = new Response;

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
        $facile = m::mock('\Orchestra\Facile\Facile');
        $facile->shouldReceive('getFormat')->andReturn('json')
            ->shouldReceive('render')->once()->andReturn('foo');
        $content->shouldReceive('getStatusCode')->once()->andReturn(200)
            ->shouldReceive('getContent')->once()->andReturn($facile)
            ->shouldReceive('isSuccessful')->never()->andReturn(true);
        $headers->shouldReceive('get')->with('Content-Type')->never()->andReturn('text/json');
        $this->assertEquals('foo', $stub->call($content));

        $content = m::mock('\Illuminate\Http\Response');
        $content->headers = $headers = m::mock('HeaderBag');
        $content->shouldReceive('getStatusCode')->once()->andReturn(200)
            ->shouldReceive('getContent')->once()->andReturn('foo')
            ->shouldReceive('isSuccessful')->never()->andReturn(true);
        $headers->shouldReceive('get')->with('Content-Type')->once()->andReturn('application/json');
        $this->assertEquals($content, $stub->call($content));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given
     * Illuminate\Http\Response with 500 status.
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testCallMethodWhenGivenIlluminateResponseWith500Status()
    {
        $stub = new Response;

        $content = m::mock('\Illuminate\Http\Response');
        $content->headers = $headers = m::mock('HeaderBag');
        $content->shouldReceive('getStatusCode')->once()->andReturn(500)
            ->shouldReceive('getContent')->once()->andReturn('foo')
            ->shouldReceive('isSuccessful')->once()->andReturn(false);
        $headers->shouldReceive('get')->with('Content-Type')->once()->andReturn('text/html');
        $this->assertEquals($content, $stub->call($content));
    }

    /**
     * Test Orchestra\Resources\Response::call() method when given string.
     *
     * @test
     */
    public function testCallMethodWhenGivenString()
    {
        $stub = new Response;
        $this->assertEquals('Foo', $stub->call('Foo'));
    }
}

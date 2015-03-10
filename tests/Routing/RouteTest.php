<?php namespace Orchestra\Resources\Routing\TestCase;

use Mockery as m;
use Orchestra\Resources\Routing\Route;

class RouteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Resources\Routing\Route::overrideParameters() method.
     *
     * @test
     */
    public function testOverrideParametersMethod()
    {
        $stub = new Route('GET', 'laravel/framework', ['uses' => 'FooController']);

        $refl       = new \ReflectionObject($stub);
        $parameters = $refl->getProperty('parameters');
        $parameters->setAccessible(true);

        $this->assertNull($parameters->getValue($stub));

        $expected = ['foo' => 'bar'];

        $stub->overrideParameters($expected);

        $this->assertEquals($expected, $parameters->getValue($stub));
    }
}

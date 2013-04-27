<?php namespace Orchestra\Resources\Tests;

use Mockery as m;

class EnvironmentTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		m::close();
	}

	/**
	 * Example test
	 *
	 * @test
	 */
	public function testExample()
	{
		$this->assertTrue(true);
	}
}

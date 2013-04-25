<?php namespace Orchestra\Resources\Tests;

class EnvironmentTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Teardown the test environment.
	 */
	public function tearDown()
	{
		\Mockery::close();
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
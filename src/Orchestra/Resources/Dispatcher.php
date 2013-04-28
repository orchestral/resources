<?php namespace Orchestra\Resources;

use InvalidArgumentException;
use Orchestra\Support\Str;

class Dispatcher {

	/**
	 * Application instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * Construct a new Resources instance.
	 *
	 * @access public
	 * @param  Illuminate\Foundation\Application    $app
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	/**
	 * Create a new dispatch.
	 *
	 * @access public
	 * @param  array    $driver
	 * @param  string   $child
	 * @param  array    $parameters
	 * @return void
	 */
	public function call($driver, $child = null, $parameters)
	{
		if ( ! is_null($child))
		{
			$uses = isset($driver->childs[$child]) ? $driver->childs[$child] : null;
		}
		else
		{
			$uses = $driver->uses;
		}

		// This would cater request to valid resource but pointing to an
		// invalid child. We should show a 404 response to the user on this
		// case.
		if (is_null($uses)) return false;

		$controller = $uses;
		$type       = 'restful';

		if (false !== strpos($uses, ':'))
		{
			list($type, $controller) = explode(':', $uses, 2);
		}

		$controller = $this->app->make($controller);
		$method     = $this->app['request']->getMethod();

		list($action, $parameters) = $this->findRoutableAttributes($type, $method, $parameters);

		return $controller->callAction($this->app, $this->app['router'], $action, $parameters);
	}

	/**
	 * Find route action and parameters content attributes from either 
	 * restful or resources routing.
	 *
	 * @access protected
	 * @param  string   $type       Either 'restful' or 'resource'
	 * @param  string   $method
	 * @param  array    $parameters
	 * @return array
	 */
	protected function findRoutableAttributes($type = 'restful', $method = null, $parameters)
	{
		$action = null;
		$method = Str::lower($method);

		if ($type === 'restful')
		{
			$action = (count($parameters) > 0 ? array_shift($parameters) : 'index');
			$action = Str::camel("{$method}_{$action}");
		} 
		else 
		{
			throw new InvalidArgumentException("Type [{$type}] not implemented.");
		}

		return array($action, $parameters);
	}
}

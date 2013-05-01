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
	 * @param  string   $name
	 * @param  array    $parameters
	 * @return void
	 */
	public function call($driver, $name = null, $parameters)
	{
		$nested     = $this->getNestedParameters($name, $parameters);		
		$nestedName = implode('.', array_keys($nested));

		if ( ! is_null($name))
		{
			if (isset($driver->childs[$nestedName]) 
				and starts_with($driver->childs[$nestedName], 'resource:'))
			{
				$uses = $driver->childs[$nestedName];
			}
			else 
			{
				$uses = (isset($driver->childs[$name]) ? $driver->childs[$name] : null);
			}
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

		list($action, $parameters) = $this->findRoutableAttributes($type, $nested, $method, $parameters);

		return $controller->callAction($this->app, $this->app['router'], $action, $parameters);
	}

	/**
	 * Find nested parameters from route.
	 *
	 * @access protected
	 * @param  string   $name
	 * @param  array    $parameters
	 * @return array
	 */
	protected function getNestedParameters($name, $parameters)
	{
		$reserved = array('create', 'show', 'index', 'delete', 'destroy', 'edit');
		$nested = array();

		if (($nestedCount = count($parameters)) > 0)
		{
			$nested = array($name => $parameters[0]);

			for ($index = 1; $index < $nestedCount; $index += 2)
			{
				$value = null;
				
				if (($index + 1) < $nestedCount) $value = $parameters[($index + 1)];
				$key = $parameters[$index];
				
				! in_array($key, $reserved) and $nested[$key] = $value;
			}
		}

		return $nested;
	}

	/**
	 * Find route action and parameters content attributes from either 
	 * restful or resources routing.
	 *
	 * @access protected
	 * @param  string   $type       Either 'restful' or 'resource'
	 * @param  integer  $nested
	 * @param  string   $method
	 * @param  array    $parameters
	 * @return array
	 */
	protected function findRoutableAttributes($type = 'restful', $nested = array(), $method = null, $parameters)
	{
		$action = null;
		$method = Str::lower($method);

		switch ($type)
		{
			case 'restful' :
				$action = (count($parameters) > 0 ? array_shift($parameters) : 'index');
				$action = Str::camel("{$method}_{$action}");
				break;
			case 'resource' :
				$resources     = array_keys($nested);
				$lastParameter = array_pop($parameters);
				$parameters = array_values($nested);

				switch ($method)
				{
					case 'get' : 
						if (in_array($lastParameter, array('edit', 'create')))
						{
							$action = $lastParameter;
						}
						elseif ( ! in_array($lastParameter, $resources)) $action = 'show';
						else $action = 'index';
						break;

					case 'post' : 
						$action = 'store'; 
						break;
					case 'put' :
					case 'patch' : 
						$action = 'update'; 
						break;
					case 'delete' : 
						$action = 'destroy'; 
						break;
				}

				break;
			default :
				throw new InvalidArgumentException("Type [{$type}] not implemented.");
		}

		return array($action, $parameters);
	}
}
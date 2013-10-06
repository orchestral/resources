<?php namespace Orchestra\Resources;

use InvalidArgumentException;
use Orchestra\Resources\Routing\Route;
use Orchestra\Resources\Routing\ControllerDispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Orchestra\Support\Str;

class Dispatcher {

	/**
	 * Application instance.
	 *
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * Router instance.
	 *
	 * @var \Illuminate\Routing\Router
	 */
	protected $router = null;

	/**
	 * Request instance.
	 *
	 * @var \Illuminate\Http\Request
	 */
	protected $request = null;

	/**
	 * Construct a new Resources instance.
	 *
	 * @param  \Illuminate\Foundation\Application   $app
	 * @param  \Illuminate\Routing\Router           $router
	 * @param  \Illuminate\Http\Request             $request
	 * @return void
	 */
	public function __construct($app, Router $router, Request $request)
	{
		$this->app     = $app;
		$this->router  = $router;
		$this->request = $request;
	}
	
	/**
	 * Create a new dispatch.
	 *
	 * @param  array    $driver
	 * @param  string   $name
	 * @param  array    $parameters
	 * @return mixed
	 */
	public function call($driver, $name = null, array $parameters)
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
		
		// Get HTTP verb used in this request.
		$verb = $this->request->getMethod();

		// Next we need to the action and parameters before we can call 
		// the destination controller, the resolver would determine both 
		// restful and resource controller.
		list($action, $parameters) = $this->findRoutableAttributes($type, $nested, $verb, $parameters);

		$route = new Route($verb, "{$driver->id}/{$name}", array('uses' => $controller));
		$route->overrideParameters($parameters);

		// Resolve the controller from container.
		$dispatcher = new ControllerDispatcher($this->router, $this->app);
		
		return $dispatcher->run($controller, $action, $route, $this->request);
	}

	/**
	 * Find nested parameters from route.
	 *
	 * @param  string   $name
	 * @param  array    $parameters
	 * @return array
	 */
	protected function getNestedParameters($name, $parameters)
	{
		$reserved = array('create', 'show', 'index', 'delete', 'destroy', 'edit');
		$nested   = array();

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
	 * @param  string   $type       Either 'restful' or 'resource'
	 * @param  integer  $nested
	 * @param  string   $verb
	 * @param  array    $parameters
	 * @return array
	 */
	protected function findRoutableAttributes($type = 'restful', $nested = array(), $verb = null, $parameters)
	{
		$action = null;
		$verb   = Str::lower($verb);

		if (in_array($type, array('restful', 'resource')))
		{
			$method = 'find'.Str::studly($type).'Routable';

			list($action, $parameters) = call_user_func(array($this, $method), $verb, $parameters, $nested);
		}
		else
		{
			throw new InvalidArgumentException("Type [{$type}] not implemented.");
		}

		return array($action, $parameters);
	}

	/**
	 * Resolve action from restful controller.
	 * 
	 * @param  string   $verb
	 * @param  array    $parameters
	 * @return array
	 */
	protected function findRestfulRoutable($verb, $parameters = array())
	{
		$action = (count($parameters) > 0 ? array_shift($parameters) : 'index');
		$action = Str::camel("{$verb}_{$action}");

		return array($action, $parameters);
	}

	/**
	 * Resolve action from resource controller.
	 * 
	 * @param  string   $verb
	 * @param  array    $parameters
	 * @param  array    $nested
	 * @return array
	 */
	protected function findResourceRoutable($verb, $parameters = array(), $nested = array())
	{
		$last       = array_pop($parameters);
		$resources  = array_keys($nested);
		$parameters = array_values($nested);

		$swappable = array(
			'post'   => 'store',
			'put'    => 'update',
			'patch'  => 'update',
			'delete' => 'destroy',
		);

		if (isset($swappable[$verb]))
		{
			$action = $swappable[$verb];
		}
		// Handle all possible GET routing.
		elseif (in_array($last, array('edit', 'create', 'delete')))
		{
			$action = $last;
		}
		elseif ( ! in_array($last, $resources) and ! empty($nested))
		{
			$action = 'show';
		}
		else 
		{
			$action = 'index';
		}

		return array($action, $parameters);
	}
}

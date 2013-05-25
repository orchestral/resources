<?php namespace Orchestra\Resources;

use InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Orchestra\Support\Str;

class Dispatcher {

	/**
	 * Application instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * Router instance.
	 *
	 * @var Illuminate\Routing\Router
	 */
	protected $router = null;

	/**
	 * Request instance.
	 *
	 * @var Illuminate\Http\Request
	 */
	protected $request = null;

	/**
	 * Construct a new Resources instance.
	 *
	 * @access public
	 * @param  Illuminate\Foundation\Application    $app
	 * @param  Illuminate\Routing\Router            $router
	 * @param  Illuminate\Http\Request              $request
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
		
		$verb = $this->request->getMethod();

		list($action, $parameters) = $this->findRoutableAttributes($type, $nested, $verb, $parameters);

		$controller = $this->app->make($controller);

		return $controller->callAction($this->app, $this->router, $action, $parameters);
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
	 * @access protected
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

		switch ($type)
		{
			case 'restful' :
				$action = (count($parameters) > 0 ? array_shift($parameters) : 'index');
				$action = Str::camel("{$verb}_{$action}");
				break;
			case 'resource' :
				$action = $this->findResourceRoutable($verb, $parameters, $nested);

				break;
			default :
				throw new InvalidArgumentException("Type [{$type}] not implemented.");
		}

		return array($action, $parameters);
	}

	/**
	 * Resolve action from resource controller.
	 * 
	 * @access protected
	 * @param  string   $verb
	 * @param  array    $parameters
	 * @param  array    $nested
	 * @return string
	 */
	protected function findResourceRoutable($verb, $parameters = array(), $nested = array())
	{
		$last       = array_pop($parameters);
		$resources  = array_keys($nested);
		$parameters = array_values($nested);

		switch ($verb)
		{
			case 'get' : 
				switch (true)
				{
					case in_array($last, array('edit', 'create', 'delete')) : 
						$action = $last;
						break;
					case ( ! in_array($last, $resources) and ! empty($nested)) :
						$action = 'show';
						break;
					default :
						$action = 'index';
						break;
				}
				
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

		return $action;
	}
}

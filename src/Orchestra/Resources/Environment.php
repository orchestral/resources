<?php namespace Orchestra\Resources;

use Closure;

class Environment {

	/**
	 * Application instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * Dispatcher instance.
	 *
	 * @var Orchestra\Resources\Dispatcher
	 */
	protected $dispatcher = null;

	/**
	 * Response instance.
	 *
	 * @var Orchestra\Resources\Response
	 */
	protected $response = null;

	/**
	 * The array of created "drivers".
	 *
	 * @var array
	 */
	protected $drivers = array();

	/**
	 * Construct a new Resources instance.
	 *
	 * @access public
	 * @param  Illuminate\Foundation\Application    $app
	 * @return void
	 */
	public function __construct($app, Dispatcher $dispatcher, Response $response)
	{
		$this->app        = $app;
		$this->dispatcher = $dispatcher;
		$this->response   = $response;
	}

	/**
	 * Register a new resource
	 *
	 * @access public
	 * @param  string   $name
	 * @param  mixed    $attributes
	 * @return Orchestra\Resources\Container
	 */
	public function make($name, $attributes)
	{
		return $this->drivers[$name] = new Container($name, $attributes);
	}

	/**
	 * Get resource by given name, or create a new one.
	 *
	 * @access public
	 * @param  string   $name
	 * @param  mixed    $attributes
	 * @return self
	 */
	public function of($name, $attributes = null)
	{
		if ( ! isset($this->drivers[$name]))
		{
			return $this->make($name, $attributes);
		}

		return $this->drivers[$name];
	}

	/**
	 * Call a resource controller and action.
	 *
	 * @access public
	 * @param  string   $name
	 * @param  array    $parameters
	 * @return Response
	 */
	public function call($name, $parameters = array())
	{
		$child = null;

		// Available drivers does not include childs, we should split the 
		// name into two (parent.child) where parent would be the name of 
		// the resource.
		if (false !== strpos($name, '.'))
		{
			list($name, $child) = explode('.', $name, 2);
		}

		// When the resources is not available, or register we should 
		// return false to indicate this status. This would allow the callee 
		// to return 404 abort status.
		if ( ! isset($this->drivers[$name])) return false;

		return $this->dispatcher->call($this->drivers[$name], $child, $parameters);
	}

	/**
	 * Handle response from resources.
	 *
	 * @access public
	 * @param  mixed    $content
	 * @param  Closure  $callback
	 * @return Illuminate\Http\Response
	 */
	public function response($content, Closure $callback = null)
	{
		return $this->response->call($content, $callback);
	}

	/**
	 * Get all registered resources.
	 *
	 * @access public
	 * @return array
	 */
	public function all()
	{
		return $this->drivers;
	}
}

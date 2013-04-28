<?php namespace Orchestra\Resources;

use Closure;
use Orchestra\Support\Str;

class Environment {

	/**
	 * Application instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app = null;

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
	public function __construct($app)
	{
		$this->app = $app;
	}

	/**
	 * Register a new resource
	 *
	 * @access public
	 * @param  string   $name
	 * @param  mixed    $options
	 * @return Orchestra\Resources\Container
	 */
	public function make($name, $options)
	{
		$schema = array(
			'name'    => '',
			'uses'    => '',
			'childs'  => array(),
			'visible' => true,
		);

		if ( ! is_array($options))
		{
			$uses    = $options;
			$options = array(
				'name' => Str::title($name),
				'uses' => $uses,
			);
		}

		$options['id'] = $name;
		$options       = array_merge($schema, $options);

		if (empty($options['name']) or empty($options['uses']))
		{
			throw new InvalidArgumentException("Required `name` and `uses` are missing.");
		}

		return $this->drivers[$name] = new Container($options);
	}

	/**
	 * Get resource by given name, or create a new one.
	 *
	 * @access public
	 * @param  string   $name
	 * @param  mixed    $options
	 * @return self
	 */
	public function of($name, $options = null)
	{
		if ( ! isset($this->drivers[$name]))
		{
			return $this->make($name, $options ?: '#');
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

		$dispatcher = new Dispatcher($this->app);

		return $dispatcher->call($this->drivers[$name], $child, $parameters);
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
		$response = new Response($this->app);

		return $response->call($content, $callback);
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

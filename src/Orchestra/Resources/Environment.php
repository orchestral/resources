<?php namespace Orchestra\Resources;

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
}

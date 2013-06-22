<?php namespace Orchestra\Resources;

use InvalidArgumentException;
use ArrayAccess;
use Orchestra\Support\Str;

class Container implements ArrayAccess {

	/**
	 * Resource attributes.
	 *
	 * @var array
	 */
	protected $attributes = array();

	/**
	 * Reserved keywords.
	 *
	 * @var array
	 */
	protected $reserved = array('index', 'visible');
	
	/**
	 * Construct a new Resouce container.
	 *
	 * @access public
	 * @param  string   $name
	 * @param  mixed    $attributes
	 * @return void
	 * @throws \InvalidArgumentException
	 */
	public function __construct($name, $attributes)
	{
		$schema = array(
			'name'    => '',
			'uses'    => '',
			'childs'  => array(),
			'visible' => true,
		);

		if ( ! is_array($attributes))
		{
			$uses    = $attributes;
			$attributes = array(
				'name' => Str::title($name),
				'uses' => $uses,
			);
		}

		$attributes['id'] = $name;

		$attributes = array_merge($schema, $attributes);

		if (empty($attributes['name']) or empty($attributes['uses']))
		{
			throw new InvalidArgumentException("Required `name` and `uses` are missing.");
		}

		$this->attributes = $attributes;
	}

	/**
	 * Map a child resource attributes.
	 *
	 * @access public
	 * @param  string   $name
	 * @param  string   $uses
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public function route($name, $uses)
	{
		if (in_array($name, $this->reserved))
		{
			throw new InvalidArgumentException("Unable to use reserved keyword [{$name}].");
		}

		if (Str::contains($name, '/'))
		{
			throw new InvalidArgumentException("Invalid character in resource name [{$name}].");
		}

		$this->attributes['childs'][$name] = $uses;

		return $this;
	}

	/**
	 * Set visibility state based on parameter.
	 *
	 * @access public
	 * @param  boolean  $value
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public function visibility($value)
	{
		if ( ! is_bool($value))
		{
			throw new InvalidArgumentException("Inpecting a boolean, [{$value}] given.");
		}
		
		$this->attributes['visible'] = $value;
		
		return $this;
	}

	/**
	 * Set visibility state to show.
	 * 
	 * @access public
	 * @return self
	 */
	public function show()
	{
		return $this->visibility(true);
	}

	/**
	 * Set visibility state to hidden.
	 *
	 * @access public
	 * @return self
	 */
	public function hide()
	{
		return $this->visibility(false);
	}

	/**
	 * Dynamically retrieve the value of an attributes.
	 */
	public function __get($key)
	{
		return isset($this->attributes[$key]) ? $this->attributes[$key] : null;
	}

	/**
	 * Dynamically set the value of an attributes.
	 */
	public function __set($key, $value)
	{
		$this->route($key, $value);
	}

	/**
	 * Handle dynamic calls to the container to set attributes.
	 */
	public function __call($method, $parameters)
	{
		if( ! empty($parameters))
		{
			throw new InvalidArgumentException("Unexpected parameters.");
		}

		return $this->attributes[$method] ?: null;
	}

	/**
	 * Determine if a given offset exists.
	 *
	 * @access public
	 * @param  string   $key
	 * @return boolean
	 */
	public function offsetExists($key)
	{
		return isset($this->attributes['childs'][$key]);
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @access public
	 * @param  string   $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->attributes['childs'][$key];
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @access public
	 * @param  string   $key
	 * @param  mixed    $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->route($key, $value);
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @access public
	 * @param  string   $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->attributes['childs'][$key]);
	}
}

<?php namespace Orchestra\Resources;

use InvalidArgumentException;
use ArrayAccess;

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
	 * @return void
	 */
	public function __construct(array $attributes)
	{
		$this->attributes = $attributes;
	}

	/**
	 * Map a child resource attributes
	 *
	 * @access public
	 * @param  string $name
	 * @param  string $uses
	 * @return self
	 */
	public function route($name, $uses)
	{
		if (in_array($name, $this->reserved))
		{
			throw new InvalidArgumentException("Unable to use reserved keyword [{$name}].");
		}

		$this->attributes['childs'][$name] = $uses;

		return $this;
	}

	/**
	 * Set visibility state based on parameter
	 *
	 * @access public
	 * @param  boolean  $value
	 * @return self
	 */
	public function visibility(boolean $value)
	{
		$this->attributes['visible'] = $value;
		return $this;
	}

	/**
	 * Set visibility state to show
	 * 
	 * @access public
	 * @return self
	 */
	public function show()
	{
		$this->attributes['visible'] = true;
		return $this;
	}

	/**
	 * Set visibility state to hidden
	 *
	 * @access public
	 * @return self
	 */
	public function hide()
	{
		$this->attributes['visible'] = false;
		return $this;
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
	 * @param  string  $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return isset($this->bindings[$key]);
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->attributes['childs'][$key];
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet($key, $value)
	{
		$this->route($key, $value);
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param  string  $key
	 * @return void
	 */
	public function offsetUnset($key)
	{
		unset($this->attributes['childs'][$key]);
	}
}

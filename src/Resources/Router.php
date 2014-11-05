<?php namespace Orchestra\Resources;

use ArrayAccess;
use Orchestra\Support\Str;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class Router implements ArrayAccess
{
    /**
     * Resource attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Reserved keywords.
     *
     * @var array
     */
    protected $reserved = ['index', 'visible'];

    /**
     * Construct a new Resouce container.
     *
     * @param  string  $name
     * @param  mixed  $attributes
     * @throws \InvalidArgumentException
     */
    public function __construct($name, $attributes)
    {
        $attributes = $this->buildResourceSchema($name, $attributes);

        if (empty($attributes['name']) || empty($attributes['uses'])) {
            throw new InvalidArgumentException("Required `name` and `uses` are missing.");
        }

        $this->attributes = $attributes;
    }

    /**
     * Map a child resource attributes.
     *
     * @param  string  $name
     * @param  string  $uses
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function route($name, $uses)
    {
        if (in_array($name, $this->reserved)) {
            throw new InvalidArgumentException("Unable to use reserved keyword [{$name}].");
        } elseif (Str::contains($name, '/')) {
            throw new InvalidArgumentException("Invalid character in resource name [{$name}].");
        }

        $this->attributes['routes'][$name] = $uses;

        return $this;
    }

    /**
     * Set visibility state based on parameter.
     *
     * @param  boolean  $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function visibility($value)
    {
        if (! is_bool($value)) {
            throw new InvalidArgumentException("Inpecting a boolean, [{$value}] given.");
        }

        $this->set('visible', $value);

        return $this;
    }

    /**
     * Set visibility state to show.
     *
     * @return $this
     */
    public function show()
    {
        return $this->visibility(true);
    }

    /**
     * Set visibility state to hidden.
     *
     * @return $this
     */
    public function hide()
    {
        return $this->visibility(false);
    }

    /**
     * Get attribute.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * Set attribute.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function set($key, $value)
    {
        Arr::set($this->attributes, $key, $value);
    }

    /**
     * Forget attribute.
     *
     * @param  string  $key
     * @return void
     */
    public function forget($key)
    {
        Arr::forget($this->attributes, $key);
    }

    /**
     * Build resource schema.
     *
     * @param  string  $name
     * @param  mixed  $attributes
     * @return array
     */
    protected function buildResourceSchema($name, $attributes)
    {
        $schema = array(
            'name'    => '',
            'uses'    => '',
            'routes'  => array(),
            'visible' => true,
        );

        if (! is_array($attributes)) {
            $uses    = $attributes;
            $attributes = array(
                'name' => Str::title($name),
                'uses' => $uses,
            );
        }

        $attributes['id'] = $name;

        return array_merge($schema, $attributes);
    }

    /**
     * Dynamically retrieve the value of an attributes.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Dynamically set the value of an attributes.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->route($key, $value);
    }

    /**
     * Handle dynamic calls to the container to set attributes.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function __call($method, $parameters)
    {
        if (! empty($parameters)) {
            throw new InvalidArgumentException("Parameters is not available.");
        }

        return $this->attributes[$method] ?: null;
    }

    /**
     * Determine if a given offset exists.
     *
     * @param  string  $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return isset($this->attributes['routes'][$key]);
    }

    /**
     * Get the value at a given offset.
     *
     * @param  string  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->attributes['routes'][$key];
    }

    /**
     * Set the value at a given offset.
     *
     * @param  string  $key
     * @param  mixed  $value
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
        unset($this->attributes['routes'][$key]);
    }
}

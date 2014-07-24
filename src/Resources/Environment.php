<?php namespace Orchestra\Resources;

use Closure;
use InvalidArgumentException;
use Orchestra\Support\Str;

class Environment
{
    /**
     * Dispatcher instance.
     *
     * @var \Orchestra\Resources\Dispatcher
     */
    protected $dispatcher;

    /**
     * Response instance.
     *
     * @var \Orchestra\Resources\Response
     */
    protected $response;

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = array();

    /**
     * Construct a new Resources instance.
     *
     * @param  \Orchestra\Resources\Dispatcher $dispatcher
     * @param  \Orchestra\Resources\Response   $response
     */
    public function __construct(Dispatcher $dispatcher, Response $response)
    {
        $this->dispatcher = $dispatcher;
        $this->response   = $response;
    }

    /**
     * Register a new resource.
     *
     * @param  string   $name
     * @param  mixed    $attributes
     * @return \Orchestra\Resources\Container
     * @throws \InvalidArgumentException
     */
    public function make($name, $attributes)
    {
        if (Str::contains($name, '.') || Str::contains($name, '/')) {
            throw new InvalidArgumentException("Invalid character in resource name [{$name}].");
        }

        return $this->drivers[$name] = new Container($name, $attributes);
    }

    /**
     * Get resource by given name, or create a new one.
     *
     * @param  string   $name
     * @param  mixed    $attributes
     * @return \Orchestra\Resources\Container
     */
    public function of($name, $attributes = null)
    {
        if (! isset($this->drivers[$name])) {
            return $this->make($name, $attributes);
        }

        return $this->drivers[$name];
    }

    /**
     * Call a resource controller and action.
     *
     * @param  string   $name
     * @param  array    $parameters
     * @return \Orchestra\Resources\Response
     */
    public function call($name, $parameters = array())
    {
        $child = null;

        // Available drivers does not include childs, we should split the
        // name into two (parent.child) where parent would be the name of
        // the resource.
        if (false !== strpos($name, '.')) {
            list($name, $child) = explode('.', $name, 2);
        }

        // When the resources is not available, or register we should
        // return false to indicate this status. This would allow the callee
        // to return 404 abort status.
        if (! isset($this->drivers[$name])) {
            return false;
        }

        return $this->dispatcher->call($this->drivers[$name], $child, $parameters);
    }

    /**
     * Handle response from resources.
     *
     * @param  mixed    $content
     * @param  Closure  $callback
     * @return mixed
     */
    public function response($content, Closure $callback = null)
    {
        return $this->response->call($content, $callback);
    }

    /**
     * Get all registered resources.
     *
     * @return array
     */
    public function all()
    {
        return $this->drivers;
    }
}

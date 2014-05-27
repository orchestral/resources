<?php namespace Orchestra\Resources;

use InvalidArgumentException;
use Orchestra\Resources\Routing\Route;
use Orchestra\Resources\Routing\ControllerDispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Orchestra\Support\Str;

class Dispatcher
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * Request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Construct a new Resources instance.
     *
     * @param  \Illuminate\Foundation\Application   $app
     * @param  \Illuminate\Routing\Router           $router
     * @param  \Illuminate\Http\Request             $request
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
     * @param  Container   $driver
     * @param  string      $name
     * @param  array       $parameters
     * @return mixed
     */
    public function call(Container $driver, $name = null, array $parameters = array())
    {
        $resolver = $this->resolveDispatchDependencies($driver, $name, $parameters);

        // This would cater request to valid resource but pointing to an
        // invalid child. We should show a 404 response to the user on this
        // case.
        if (! $resolver->isValid()) {
            return false;
        }

        // Get HTTP verb used in this request.
        $verb = $this->request->getMethod();

        // Next we need to the action and parameters before we can call
        // the destination controller, the resolver would determine both
        // restful and resource controller.
        list($action, $parameters) = $this->findRoutableAttributes($resolver->getType(), $verb, $parameters, $resolver->getNestedSegment());

        $route = new Route($verb, "{$driver->id}/{$name}", array('uses' => $resolver->getController()));
        $route->overrideParameters($parameters);

        // Resolve the controller from container.
        $dispatcher = new ControllerDispatcher($this->router, $this->app);

        return $dispatcher->dispatch($route, $this->request, $resolver->getController(), $action);
    }

    /**
     * Resolve dispatcher dependencies.
     *
     * @param  Container   $driver
     * @param  string      $name
     * @param  array       $parameters
     * @return Resolver
     */
    public function resolveDispatchDependencies(Container $driver, $name = null, array $parameters)
    {
        $nested     = $this->getNestedParameters($name, $parameters);
        $nestedName = implode('.', array_keys($nested));
        $uses       = $driver->uses;

        if (! is_null($name)) {
            if (isset($driver->childs[$nestedName]) && starts_with($driver->childs[$nestedName], 'resource:')) {
                $uses = $driver->childs[$nestedName];
            } else {
                $uses = (isset($driver->childs[$name]) ? $driver->childs[$name] : null);
            }
        }

        return new Resolver($nested, $uses);
    }

    /**
     * Find nested parameters from route.
     *
     * @param  string   $name
     * @param  array    $parameters
     * @return array
     */
    protected function getNestedParameters($name, array $parameters)
    {
        $reserved = array('create', 'show', 'index', 'delete', 'destroy', 'edit');
        $nested   = array();

        if (($nestedCount = count($parameters)) > 0) {
            $nested = array($name => $parameters[0]);

            for ($index = 1; $index < $nestedCount; $index += 2) {
                $value = null;

                if (($index + 1) < $nestedCount) {
                    $value = $parameters[($index + 1)];
                }

                $key = $parameters[$index];

                ! in_array($key, $reserved) && $nested[$key] = $value;
            }
        }

        return $nested;
    }

    /**
     * Find route action and parameters content attributes from either
     * restful or resources routing.
     *
     * @param  string   $type       Either 'restful' or 'resource'
     * @param  string   $verb
     * @param  array    $parameters
     * @param  array    $nested
     * @return array
     */
    protected function findRoutableAttributes($type = 'restful', $verb = null, array $parameters, array $nested)
    {
        $action = null;
        $verb   = Str::lower($verb);

        if (in_array($type, array('restful', 'resource'))) {
            $method = 'find'.Str::studly($type).'Routable';

            list($action, $parameters) = call_user_func(array($this, $method), $verb, $parameters, $nested);
        } else {
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
    protected function findRestfulRoutable($verb, array $parameters)
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
    protected function findResourceRoutable($verb, array $parameters, array $nested)
    {
        $swappable = array(
            'post' => 'store',
            'put' => 'update',
            'patch' => 'update',
            'delete' => 'destroy',
        );

        if (! isset($swappable[$verb])) {
            $action = $this->getAlternativeResourceAction($parameters, $nested);
        } else {
            $action = $swappable[$verb];
        }

        $parameters = array_values($nested);

        return array($action, $parameters);
    }

    /**
     * Get action name.
     *
     * @param  array    $parameters
     * @param  array    $nested
     * @return string
     */
    protected function getAlternativeResourceAction(array $parameters, array $nested)
    {
        $last = array_pop($parameters);
        $resources = array_keys($nested);

        if (in_array($last, array('edit', 'create', 'delete'))) {
            // Handle all possible GET routing.
            return $last;
        } elseif (!in_array($last, $resources) && !empty($nested)) {
            return 'show';
        }

        return 'index';
    }
}

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
     * @param  string|null $name
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

        return $this->dispatch($driver, $name, $resolver);
    }

    /**
     * Resolve dispatcher dependencies.
     *
     * @param  Container   $driver
     * @param  string      $name
     * @param  array       $parameters
     * @return Resolver
     */
    public function resolveDispatchDependencies(Container $driver, $name, array $parameters)
    {
        $segments = $this->getNestedParameters($name, $parameters);
        $key      = implode('.', array_keys($segments));
        $uses     = $driver->uses;

        if (! is_null($name)) {
            if (isset($driver->childs[$key]) && starts_with($driver->childs[$key], 'resource:')) {
                $uses = $driver->childs[$key];
            } else {
                $uses = (isset($driver->childs[$name]) ? $driver->childs[$name] : null);
            }
        }

        return new Resolver($uses, $this->request->getMethod(), $parameters, $segments);
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
     * @param  Resolver $resolver
     * @return array
     */
    protected function findRoutableAttributes(Resolver $resolver)
    {
        $type = $resolver->getType();

        if (in_array($type, array('restful', 'resource'))) {
            $method = 'find'.Str::studly($type).'Routable';

            list($action, $parameters) = call_user_func(array($this, $method), $resolver);
        } else {
            throw new InvalidArgumentException("Type [{$type}] not implemented.");
        }

        return array($action, $parameters);
    }

    /**
     * Resolve action from restful controller.
     *
     * @param  Resolver $resolver
     * @return array
     */
    protected function findRestfulRoutable(Resolver $resolver)
    {
        $parameters = $resolver->getParameters();
        $verb       = $resolver->getVerb();

        $action = (count($parameters) > 0 ? array_shift($parameters) : 'index');
        $action = Str::camel("{$verb}_{$action}");

        return array($action, $parameters);
    }

    /**
     * Resolve action from resource controller.
     *
     * @param  Resolver $resolver
     * @return array
     */
    protected function findResourceRoutable(Resolver $resolver)
    {
        $verb      = $resolver->getVerb();
        $swappable = array(
            'post' => 'store',
            'put' => 'update',
            'patch' => 'update',
            'delete' => 'destroy',
        );

        if (! isset($swappable[$verb])) {
            $action = $this->getAlternativeResourceAction($resolver);
        } else {
            $action = $swappable[$verb];
        }

        $parameters = array_values($resolver->getSegments());

        return array($action, $parameters);
    }

    /**
     * Get action name.
     *
     * @param  Resolver $resolver
     * @return string
     */
    protected function getAlternativeResourceAction(Resolver $resolver)
    {
        $parameters = $resolver->getParameters();
        $segments   = $resolver->getSegments();

        $last       = array_pop($parameters);
        $resources  = array_keys($segments);

        if (in_array($last, array('edit', 'create', 'delete'))) {
            // Handle all possible GET routing.
            return $last;
        } elseif (!in_array($last, $resources) && !empty($segments)) {
            return 'show';
        }

        return 'index';
    }

    /**
     * Dispatch the resource.
     * 
     * @param Container $driver
     * @param string    $name
     * @param Resolver  $resolver
     * @return mixed
     */
    protected function dispatch(Container $driver, $name, Resolver $resolver)
    {
        // Next we need to the action and parameters before we can call
        // the destination controller, the resolver would determine both
        // restful and resource controller.
        list($action, $parameters) = $this->findRoutableAttributes($resolver);

        $route = new Route($resolver->getVerb(), "{$driver->id}/{$name}", array('uses' => $resolver->getController()));
        $route->overrideParameters($parameters);

        // Resolve the controller from container.
        $dispatcher = new ControllerDispatcher($this->router, $this->app);

        return $dispatcher->dispatch($route, $this->request, $resolver->getController(), $action);
    }
}

<?php namespace Orchestra\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Orchestra\Resources\Routing\Route;
use Illuminate\Routing\Router as IlluminateRouter;
use Orchestra\Resources\Routing\ControllerDispatcher;
use Illuminate\Contracts\Container\Container as IlluminateContainer;

class Dispatcher
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Container\Container
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
     * @param  \Illuminate\Contracts\Container\Container  $app
     * @param  \Illuminate\Routing\Router  $router
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(IlluminateContainer $app, IlluminateRouter $router, Request $request)
    {
        $this->app     = $app;
        $this->router  = $router;
        $this->request = $request;
    }

    /**
     * Create a new dispatch.
     *
     * @param  \Orchestra\Resources\Router  $driver
     * @param  string|null  $name
     * @param  array  $parameters
     *
     * @return mixed
     */
    public function call(Router $driver, $name = null, array $parameters = [])
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
     * @param  \Orchestra\Resources\Router  $driver
     * @param  string  $name
     * @param  array  $parameters
     *
     * @return \Orchestra\Resources\Resolver
     */
    public function resolveDispatchDependencies(Router $driver, $name, array $parameters)
    {
        $segments = $this->getNestedParameters($name, $parameters);
        $key      = implode('.', array_keys($segments));
        $uses     = $driver->get('uses');

        if (! is_null($name)) {
            if (isset($driver[$key]) && Str::startsWith($driver[$key], 'resource:')) {
                $uses = $driver[$key];
            } else {
                $uses = (isset($driver[$name]) ? $driver[$name] : null);
            }
        }

        return new Resolver($uses, $this->request->getMethod(), $parameters, $segments);
    }

    /**
     * Find nested parameters from route.
     *
     * @param  string  $name
     * @param  array  $parameters
     *
     * @return array
     */
    protected function getNestedParameters($name, array $parameters)
    {
        $reserved = ['create', 'show', 'index', 'delete', 'destroy', 'edit'];
        $nested   = [];

        if (($nestedCount = count($parameters)) > 0) {
            $nested = [$name => $parameters[0]];

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
     * @param  \Orchestra\Resources\Resolver  $resolver
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function findRoutableAttributes(Resolver $resolver)
    {
        $type = $resolver->getType();

        if (in_array($type, ['restful', 'resource'])) {
            $method = 'find'.Str::studly($type).'Routable';

            list($action, $parameters) = call_user_func([$this, $method], $resolver);
        } else {
            throw new InvalidArgumentException("Type [{$type}] not implemented.");
        }

        return [$action, $parameters];
    }

    /**
     * Resolve action from restful controller.
     *
     * @param  \Orchestra\Resources\Resolver  $resolver
     *
     * @return array
     */
    protected function findRestfulRoutable(Resolver $resolver)
    {
        $parameters = $resolver->getParameters();
        $verb       = $resolver->getVerb();

        $action = (count($parameters) > 0 ? array_shift($parameters) : 'index');
        $action = Str::camel("{$verb}_{$action}");

        return [$action, $parameters];
    }

    /**
     * Resolve action from resource controller.
     *
     * @param  \Orchestra\Resources\Resolver  $resolver
     *
     * @return array
     */
    protected function findResourceRoutable(Resolver $resolver)
    {
        $verb      = $resolver->getVerb();
        $swappable = [
            'post'   => 'store',
            'put'    => 'update',
            'patch'  => 'update',
            'delete' => 'destroy',
        ];

        if (! isset($swappable[$verb])) {
            $action = $this->getAlternativeResourceAction($resolver);
        } else {
            $action = $swappable[$verb];
        }

        $parameters = array_values($resolver->getSegments());

        return [$action, $parameters];
    }

    /**
     * Get action name.
     *
     * @param  \Orchestra\Resources\Resolver  $resolver
     *
     * @return string
     */
    protected function getAlternativeResourceAction(Resolver $resolver)
    {
        $parameters = $resolver->getParameters();
        $segments   = $resolver->getSegments();

        $last       = array_pop($parameters);
        $resources  = array_keys($segments);

        if (in_array($last, ['edit', 'create', 'delete'])) {
            // Handle all possible GET routing.
            return $last;
        } elseif (! in_array($last, $resources) && ! empty($segments)) {
            return 'show';
        }

        return 'index';
    }

    /**
     * Dispatch the resource.
     *
     * @param  \Orchestra\Resources\Router  $driver
     * @param  string  $name
     * @param  \Orchestra\Resources\Resolver  $resolver
     *
     * @return mixed
     */
    protected function dispatch(Router $driver, $name, Resolver $resolver)
    {
        // Next we need to the action and parameters before we can call
        // the destination controller, the resolver would determine both
        // restful and resource controller.
        list($action, $parameters) = $this->findRoutableAttributes($resolver);

        $route = new Route((array) $resolver->getVerb(), "{$driver->get('id')}/{$name}", ['uses' => $resolver->getController()]);
        $route->overrideParameters($parameters);

        // Resolve the controller from container.
        $dispatcher = new ControllerDispatcher($this->router, $this->app);

        return $dispatcher->dispatch($route, $this->request, $resolver->getController(), $action);
    }
}

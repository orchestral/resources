<?php namespace Orchestra\Resources;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class ResourcesServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var boolean
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('orchestra.resources', function ($app) {
            $dispatcher = new Dispatcher($app, $app['router'], $app['request']);
            $response   = new Response($app);

            return new Environment($app, $dispatcher, $response);
        });

        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Orchestra\Resources', 'Orchestra\Support\Facades\Resources');
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('orchestra.resources');
    }
}

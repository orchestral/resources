<?php namespace Orchestra\Resources;

use Illuminate\Support\ServiceProvider;

class ResourcesServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('orchestra.resources', function ($app) {
            $dispatcher = new Dispatcher($app, $app->make('router'), $app->make('request'));

            return new Factory($dispatcher, new Response());
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['orchestra.resources'];
    }
}

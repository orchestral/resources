<?php namespace Orchestra\Resources;

use Illuminate\Support\ServiceProvider;

class ResourcesServiceProvider extends ServiceProvider {

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
		$this->app['orchestra.resources'] = $this->app->share(function ($app)
		{
			$dispatcher = new Dispatcher($app, $app['router'], $app['request']);
			$response   = new Response($app);
			
			return new Environment($app, $dispatcher, $response);
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

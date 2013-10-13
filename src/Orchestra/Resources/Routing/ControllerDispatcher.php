<?php namespace Orchestra\Resources\Routing;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ControllerDispatcher extends \Illuminate\Routing\ControllerDispatcher {
	
	/**
	 * Prepare to route to controller.
	 * 
     * @param  string                       $controller
     * @param  string                       $method
     * @param  \Illuminate\Routing\Route    $route
     * @param  \Illuminate\Http\Request     $request
     * @return ControllerDispatcher::dispatch()
	 */
	public function run($controller, $method, $route, $request)
	{
		return $this->dispatch($route, $request, $controller, $method);
	}

	/**
     * {@inheritdoc}
     */
    protected function call($instance, $route, $method)
    {
    	$controller = get_class($instance);

    	if ( ! method_exists($instance, $method))
    	{
    		throw new NotFoundHttpException("Unable to call [{$controller}@{$method}].");
    	}

    	return parent::call($instance, $route, $method);
    }
}

<?php namespace Orchestra\Resources\Routing;

class ControllerDispatcher extends \Illuminate\Routing\ControllerDispatcher {
	
	/**
	 * Prepare to route to controller.
	 * 
     * @param  string                       $controller
     * @param  string                       $method
     * @param  \Illuminate\Routing\Route    $route
     * @param  \Illuminate\Http\Request     $request
     * @return see::dispatch()
	 */
	public function run($controller, $method, $route, $request)
	{
		return $this->dispatch($route, $request, $controller, $method);
	}
}

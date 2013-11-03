<?php namespace Orchestra\Resources\Routing;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ControllerDispatcher extends \Illuminate\Routing\ControllerDispatcher
{
    /**
     * {@inheritdoc}
     */
    protected function call($instance, $route, $method)
    {
        $controller = get_class($instance);

        if (! method_exists($instance, $method)) {
            throw new NotFoundHttpException("Unable to call [{$controller}@{$method}].");
        }

        return parent::call($instance, $route, $method);
    }
}

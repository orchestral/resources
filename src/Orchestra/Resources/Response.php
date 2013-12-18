<?php namespace Orchestra\Resources;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as IlluminateResponse;
use Orchestra\Facile\Response as FacileResponse;

class Response
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Construct a new Resources instance.
     *
     * @param  \Illuminate\Foundation\Application   $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Handle response from resources.
     *
     * @param  mixed    $content
     * @param  \Closure $callback
     * @return mixed
     */
    public function call($content, Closure $callback = null)
    {
        if (false === $content or is_null($content)) {
            return $this->app->abort(404);
        } elseif ($content instanceof RedirectResponse or $content instanceof JsonResponse) {
            return $content;
        } elseif ($content instanceof FacileResponse) {
            return $content->render();
        } elseif ($content instanceof IlluminateResponse) {
            return $this->handleIlluminateResponse($content, $callback);
        }

        return $this->handleResponseCallback($content, $callback);
    }

    /**
     * Handle Illuminate\Http\Response content.
     *
     * @param  \Illuminate\Http\Response    $content
     * @param  \Closure                     $callback
     * @return mixed
     */
    protected function handleIlluminateResponse($content, Closure $callback = null)
    {
        $statusCode  = $content->getStatusCode();
        $response    = $content->getContent();
        $contentType = $content->headers->get('Content-Type');
        $isHtml      = starts_with($contentType, 'text/html');

        if ($response instanceof FacileResponse and $response->getFormat() !== 'html') {
            return $response->render();
        } elseif (! is_null($contentType) and ! $isHtml) {
            return $content;
        } elseif (! $content->isSuccessful()) {
            return $this->app->abort($statusCode);
        }

        return $this->handleResponseCallback($response, $callback);
    }

    /**
     * Handle response callback.
     *
     * @param  mixed    $content
     * @param  \Closure $callback
     * @return mixed
     */
    protected function handleResponseCallback($content, Closure $callback = null)
    {
        if ($callback instanceof Closure) {
            return call_user_func($callback, $content);
        }

        return $content;
    }
}

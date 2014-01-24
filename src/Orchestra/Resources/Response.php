<?php namespace Orchestra\Resources;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as IlluminateResponse;
use Orchestra\Facile\Response as FacileResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Response
{
    /**
     * Handle response from resources.
     *
     * @param  mixed    $content
     * @param  \Closure $callback
     * @return mixed
     */
    public function call($content, Closure $callback = null)
    {
        if (false === $content) {
            return $this->abort(404);
        } elseif (is_null($content)) {
            return new IlluminateResponse($content, 200);
        } elseif ($content instanceof RedirectResponse || $content instanceof JsonResponse) {
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
     * @param  \Illuminate\Http\Response   $content
     * @param  \Closure                    $callback
     * @return mixed
     */
    protected function handleIlluminateResponse(IlluminateResponse $content, Closure $callback = null)
    {
        $code        = $content->getStatusCode();
        $response    = $content->getContent();
        $contentType = $content->headers->get('Content-Type');
        $isHtml      = starts_with($contentType, 'text/html');

        if ($response instanceof FacileResponse && $response->getFormat() !== 'html') {
            return $response->render();
        } elseif (! is_null($contentType) && ! $isHtml) {
            return $content;
        } elseif (! $content->isSuccessful()) {
            return $this->abort($code);
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

    /**
     * Handle abort response.
     *
     * @param  integer $code
     * @param  string  $message
     * @param  array   $headers
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function abort($code, $message = '', array $headers = array())
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message);
        }

        throw new HttpException($code, $message, null, $headers);
    }
}

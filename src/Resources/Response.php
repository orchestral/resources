<?php namespace Orchestra\Resources;

use Closure;
use Orchestra\Support\Str;
use Orchestra\Facile\Facile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Response
{
    /**
     * Handle response from resources.
     *
     * @param  mixed  $content
     * @param  \Closure|null  $callback
     * @return mixed
     */
    public function call($content, Closure $callback = null)
    {
        if ($content instanceof RedirectResponse || $content instanceof JsonResponse) {
            return $content;
        } elseif ($content instanceof Facile) {
            return $content->render();
        } elseif ($content instanceof IlluminateResponse) {
            return $this->handleIlluminateResponse($content, $callback);
        }

        return $this->handleResponseCallback($content, $callback);
    }

    /**
     * Handle Illuminate\Http\Response content.
     *
     * @param  \Illuminate\Http\Response  $content
     * @param  \Closure  $callback
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function handleIlluminateResponse(IlluminateResponse $content, Closure $callback = null)
    {
        $code     = $content->getStatusCode();
        $response = $content->getContent();

        if ($this->isRenderableResponse($response)) {
            return $response->render();
        } elseif ($this->isNoneHtmlResponse($content)) {
            return $content;
        } elseif ($content->isSuccessful()) {
            return $this->handleResponseCallback($response, $callback);
        }

        return $this->abort($code);
    }

    /**
     * Handle response callback.
     *
     * @param  mixed  $content
     * @param  \Closure|null  $callback
     * @return mixed
     */
    protected function handleResponseCallback($content, Closure $callback = null)
    {
        if ($callback instanceof Closure) {
            $content = call_user_func($callback, $content);
        }

        if (false === $content) {
            return $this->abort(404);
        } elseif (is_null($content)) {
            return new IlluminateResponse($content, 200);
        }

        return $content;
    }

    /**
     * Handle abort response.
     *
     * @param  int  $code
     * @param  string  $message
     * @param  array  $headers
     * @return void
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

    /**
     * Is response renderable.
     *
     * @param  object|string  $response
     * @return bool
     */
    protected function isRenderableResponse($response)
    {
        return $response instanceof Facile && $response->getFormat() !== 'html';
    }

    /**
     * Is response none html.
     *
     * @param  \Illuminate\Http\Response  $content
     * @return bool
     */
    protected function isNoneHtmlResponse(IlluminateResponse $content)
    {
        $contentType = $content->headers->get('Content-Type');
        $isHtml      = Str::startsWith($contentType, 'text/html');

        return ! is_null($content) && ! $isHtml;
    }
}

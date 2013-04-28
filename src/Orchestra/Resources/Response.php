<?php namespace Orchestra\Resources;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as IlluminateResponse;
use Orchestra\Facile\Response as FacileResponse;

class Response {

	/**
	 * Application instance.
	 *
	 * @var Illuminate\Foundation\Application
	 */
	protected $app = null;

	/**
	 * Construct a new Resources instance.
	 *
	 * @access public
	 * @param  Illuminate\Foundation\Application    $app
	 * @return void
	 */
	public function __construct($app)
	{
		$this->app = $app;
	}
	
	/**
	 * Handle response from resources.
	 *
	 * @access public
	 * @param  mixed    $content
	 * @param  Closure  $callback
	 * @return Illuminate\Http\Response
	 */
	public function call($content, Closure $callback = null)
	{
		$response = null;
		
		switch (true)
		{
			case ( ! $content) :
				return $this->app->abort(404);
			
			case ($content instanceof RedirectResponse or $content instanceof JsonResponse) :
				return $content;

			case ($content instanceof FacileResponse) :
				return $content->render();
		
			case ($content instanceof IlluminateResponse) :
				$statusCode  = $content->getStatusCode();
				$response    = $content->getContent();
				$contentType = $content->headers->get('content-type');
				$isHtml      = starts_with($contentType, 'text/html');
				
				if ($response instanceof FacileResponse and $response->format !== 'html')
				{
					return $response->getContent()->render();
				}
				elseif ( ! is_null($contentType) and ! $isHtml)
				{
					return $response;
				}
				elseif ( ! $content->isSuccessful())
				{
					return $this->app->abort($statusCode);
				}

				break;
			default :
				// nothing to do here.
		}

		if ($callback instanceof Closure) $response = call_user_func($callback, $response);

		return $response;
	}
}

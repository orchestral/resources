<?php namespace Orchestra\Resources;

use Orchestra\Support\Str;

class Resolver
{
    /**
     * Controller class name.
     *
     * @var string
     */
    protected $controller;

    /**
     * URL Parameters.
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * URL segments.
     *
     * @var array
     */
    protected $segments = array();

    /**
     * 'resource' or 'restful' type.
     *
     * @var string
     */
    protected $type = 'restful';

    /**
     * Valid resource.
     *
     * @var boolean
     */
    protected $valid;

    /**
     * HTTP verb.
     *
     * @var string
     */
    protected $verb;

    /**
     * Construct a new resolver.
     *
     * @param  string|null  $uses
     * @param  string       $verb
     * @param  array        $parameters
     * @param  array        $segments
     */
    public function __construct($uses = null, $verb = 'get', array $parameters = array(), array $segments = array())
    {
        $controller   = $uses;
        $type         = 'restful';

        if (false !== strpos($uses, ':')) {
            list($type, $controller) = explode(':', $uses, 2);
        }

        $this->controller = $controller;
        $this->parameters = $parameters;
        $this->segments   = $segments;
        $this->type       = $type;
        $this->valid      = ! is_null($uses);
        $this->verb       = Str::lower($verb);
    }

    /**
     * Get controller.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get URL parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Get URL segments.
     *
     * @return array
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get HTTP Verb.
     *
     * @return string
     */
    public function getVerb()
    {
        return $this->verb;
    }

    /**
     * Is valid resource request.
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }
}

<?php namespace Orchestra\Resources;

class Resolver
{
    /**
     * Controller class name.
     *
     * @var string
     */
    protected $controller;

    /**
     * Nested URL.
     *
     * @var array
     */
    protected $nestedSegment = array();

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
     * Construct a new resolver.
     *
     * @param string|null   $uses
     */
    public function __construct(array $nested, $uses = null)
    {
        $controller   = $uses;
        $type         = 'restful';

        if (false !== strpos($uses, ':')) {
            list($type, $controller) = explode(':', $uses, 2);
        }

        $this->controller    = $controller;
        $this->nestedSegment = $nested;
        $this->type          = $type;
        $this->valid         = ! is_null($uses);
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
     * Get nested.
     *
     * @return array
     */
    public function getNestedSegment()
    {
        return $this->nestedSegment;
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
     * Is valid resource request.
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }
}

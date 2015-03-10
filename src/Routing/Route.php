<?php namespace Orchestra\Resources\Routing;

class Route extends \Illuminate\Routing\Route
{
    /**
     * Override the parameter list.
     *
     * @param  array    $parameters
     *
     * @return self
     */
    public function overrideParameters(array $parameters)
    {
        $this->parameters = $this->replaceDefaults($parameters);

        return $this;
    }
}

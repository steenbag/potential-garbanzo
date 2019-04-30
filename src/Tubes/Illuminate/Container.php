<?php namespace Steenbag\Tubes\Illuminate;

class Container implements \Steenbag\Tubes\Contract\Container
{

    protected $illuminateContainer;

    public function __construct(\Illuminate\Container\Container $container)
    {
        $this->illuminateContainer = $container;
    }

    /**
     * Return a concrete instance of the class.
     *
     * @param $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, $parameters = [])
    {
        return $this->illuminateContainer->make($abstract, $parameters);
    }

}

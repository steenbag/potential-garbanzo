<?php namespace Steenbag\Tubes\NullImpl;

/**
 * The null implementation library allows for a very basic implementation of
 * the contract without relying on Laravel. Only internal types are used.
 * As such, functionality is limited only to what is defined in the contract.
 *
 * Class Container
 * @package Steenbag\Tubes\NullImpl
 */
class Container implements \Steenbag\Tubes\Contract\Container
{

    /**
     * Return a concrete instance of the class.
     *
     * @param $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, $parameters = [])
    {
        $class = new \ReflectionClass($abstract);

        return $class->newInstanceArgs($parameters);
    }
}

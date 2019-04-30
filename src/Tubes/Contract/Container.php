<?php namespace Steenbag\Tubes\Contract;


interface Container
{

    /**
     * Return a concrete instance of the abstract class.
     *
     * @param $abstract
     * @param array $parameters
     * @return mixed
     */
    public function make($abstract, $parameters = []);

}

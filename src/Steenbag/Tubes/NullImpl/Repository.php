<?php namespace Steenbag\Tubes\NullImpl;

/**
 * The null implementation library allows for a very basic implementation of
 * the contract without relying on Laravel. Only internal types are used.
 * As such, functionality is limited only to what is defined in the contract.
 *
 * Class Repository
 * @package Steenbag\Tubes\NullImpl
 */
class Repository implements \Steenbag\Tubes\Contract\Repository
{

    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * Set a given configuration value.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }
}

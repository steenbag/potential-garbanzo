<?php namespace Steenbag\Tubes\NullImpl;

use Steenbag\Tubes\Contract\dynamic;

class Request implements \Steenbag\Tubes\Contract\Request
{

    protected $symfony;

    public function __construct(\Symfony\Component\HttpFoundation\Request $symfony)
    {
        $this->symfony = $symfony;
    }

    /**
     * Return the Request instance.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function instance()
    {
        return $this->symfony;
    }

    /**
     * Returns the request body content.
     *
     * @return string The request body content.
     */
    public function getContent()
    {
        return $this->symfony->getContent();
    }

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @param  dynamic  string
     * @return bool
     */
    public function is()
    {
        // TODO: Implement is() method.
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        return $this->symfony->getPathInfo();
    }

    /**
     * Get the HTTP method.
     *
     * @return string
     */
    public function method()
    {
        return $this->symfony->getMethod();
    }

    /**
     * Retrieve a header from the request.
     *
     * @param  string $key
     * @param  mixed $default
     * @return string
     */
    public function header($key = null, $default = null)
    {
        return is_null($key) ? $this->symfony->headers->all() : $this->symfony->headers->get($key, $default);
    }
}

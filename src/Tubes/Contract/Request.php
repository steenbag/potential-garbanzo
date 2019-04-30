<?php namespace Steenbag\Tubes\Contract;


interface Request
{

    /**
     * Return the Request instance.
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function instance();

    /**
     * Returns the request body content.
     *
     * @return string The request body content.
     */
    public function getContent();

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @param  dynamic  string
     * @return bool
     */
    public function is();

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path();

    /**
     * Get the HTTP method.
     *
     * @return string
     */
    public function method();

    /**
     * Retrieve a header from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string
     */
    public function header($key = null, $default = null);

}

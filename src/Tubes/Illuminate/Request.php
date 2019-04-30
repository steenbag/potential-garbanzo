<?php namespace Steenbag\Tubes\Illuminate;

class Request implements \Steenbag\Tubes\Contract\Request
{

    protected $illuminateRequest;

    public function __construct(\Illuminate\Http\Request $request)
    {
        $this->illuminateRequest = $request;
    }

    /**
     * Return the Request instance.
     *
     * @return \Illuminate\Http\Request
     */
    public function instance()
    {
        return $this->illuminateRequest->instance();
    }

    /**
     * Returns the request body content.
     *
     * @return string The request body content.
     */
    public function getContent()
    {
        return $this->illuminateRequest->getContent();
    }

    /**
     * Determine if the current request URI matches a pattern.
     *
     * @param  dynamic  string
     * @return bool
     */
    public function is()
    {
        return call_user_func_array([$this->illuminateRequest, 'is'], func_get_args());
    }

    /**
     * Get the current path info for the request.
     *
     * @return string
     */
    public function path()
    {
        return $this->illuminateRequest->path();
    }

    /**
     * Retrieve a header from the request.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return string
     */
    public function header($key = null, $default = null)
    {
        return $this->illuminateRequest->header($key, $default);
    }

    /**
     * Get the HTTP method.
     *
     * @return string
     */
    public function method()
    {
        return $this->illuminateRequest->getMethod();
    }
}

<?php namespace Steenbag\Tubes\Illuminate;

class Router implements \Steenbag\Tubes\Contract\Router
{

    protected $illuminateRouter;

    public function __construct(\Illuminate\Routing\Router $router)
    {
        $this->illuminateRouter = $router;
    }

    /**
     * Register a new route with the given verbs.
     *
     * @param  array|string $methods
     * @param  string $uri
     * @param  \Closure|array|string $action
     * @return \Illuminate\Routing\Route
     */
    public function match($methods, $uri, $action)
    {
        return $this->illuminateRouter->match($methods, $uri, $action);
    }

    /**
     * Register a new route responding to all verbs.
     *
     * @param  string $uri
     * @param  \Closure|array|string $action
     * @return \Illuminate\Routing\Route
     */
    public function any($uri, $action)
    {
        return $this->router->any($uri, $action);
    }
}

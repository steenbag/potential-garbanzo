<?php namespace Steenbag\Tubes\Generator;

use ReflectionMethod;

class ThriftParser
{

    protected $thriftClass;

    protected $thriftService = [];

    public function __construct($class)
    {
        $this->thriftClass = $class;
    }

    /**
     * Parse through all of the registered Thrift classes and get all of the methods off of them.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function parse()
    {
        $reflection = new \ReflectionClass($this->thriftClass);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $_methods = [];
        foreach ($methods as $method) {
            $_params = [];
            if ($this->includeMethod($method)) {
                $params = $method->getParameters();
                foreach ($params as $param) {
                    $_param = [
                        'name' => $param->getName(),
                        'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
                    ];
                    $_params[$param->getName()] = $_param;
                }
                $_methods[$method->getName()] = $_params;
            }
        }
        $this->thriftService = $_methods;
    }

    /**
     * Get the structured array of services.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getThriftService()
    {
        if (empty($this->thriftService)) {
            $this->parse();
        }

        return $this->thriftService;
    }

    /**
     * Determine if we should include this method in the result.
     *
     * @param ReflectionMethod $method
     * @return bool
     */
    protected function includeMethod(ReflectionMethod $method)
    {
        if ($method->getName() === '__construct' || starts_with($method->getName(), 'send_') || starts_with($method->getName(), 'recv_')) {
            return false;
        }
        return true;
    }

}

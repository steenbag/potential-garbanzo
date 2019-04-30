<?php namespace Steenbag\Tubes\Descriptor;

use Steenbag\Tubes\DocBlock\DocBlock;
use Steenbag\Tubes\WebService\ServiceInterface;

/**
 * Encapsulates the description of a Tubes Web Service.
 *
 * Class ServiceDescription
 * @package Steenbag\Tubes\Descriptor
 */
class ServiceDescription
{

    protected $service;

    protected $name;

    protected $description;

    protected $version;

    protected $methods;

    protected $clients;

    public function __construct(ServiceInterface $service)
    {
        $this->service = $service;
        $this->inspectService();
    }

    public function getMethods()
    {
        if ($this->methods === null) {
            $this->inspectService();
        }

        return $this->methods;
    }

    /**
     * Inspect the service class and load the information about it.
     */
    protected function inspectService()
    {
        $service = $this->service;
        $docBlock = DocBlock::ofClass($service);

        $this->name = $service->getName();
        $this->description = $service->getDescription();
        $this->version = $service->getVersion();
        $this->methods = [];
        $this->clients = [];
        foreach ($this->listMethods() as $method => $description) {
            $this->methods[$method] = $description;
        }
        if ($docBlock->hasTag('client')) {
            foreach ($docBlock->tag('client') as $client) {
                $this->clients[] = [
                    'package' => $client['package'],
                    'url' => $client['url']
                ];
            }
        }
    }

    /**
     * Get the information about all of the methods declared in this service.
     */
    protected function listMethods()
    {
        $_methods = [];
        $methods = $this->service->listProcedures();
        foreach ($methods as $method) {
            $reflection = new \ReflectionMethod($this->service, $method);
            $_method = DocBlock::of($reflection);
            $_methods[$method] = new MethodDescription($method, $_method, $reflection);
        }
        return $_methods;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function getClients()
    {
        return $this->clients;
    }

}

<?php namespace Steenbag\Tubes\SharedSdk;

use Steenbag\Tubes\Certificate\Certificate;
use Steenbag\Tubes\Contract\Repository;
use Steenbag\Tubes\Factory\ThriftClientFactory;
use Steenbag\Tubes\Store\Store;

abstract class BaseClient
{

    protected $type;

    protected $namespace;

    protected $certificate;

    protected $store;

    protected $endpoint;

    protected $config;

    public function __construct($endpoint, Repository $config, Certificate $certificate)
    {
        ThriftClientFactory::registerType($this->type, ['namespace' => $this->namespace]);
        $this->certificate = $certificate;
        $this->endpoint = $endpoint;
        $this->config = $config;
        $this->certificate = $certificate;
    }

    public function getThriftClientFactory()
    {
        return $this->store;
    }

    /**
     * Create a store instance.
     *
     * @param $type
     * @param null $endpoint
     * @param null $config
     * @param Certificate $certificate
     * @return Store
     */
    public function getStore($type, $endpoint = null, $config = null, Certificate $certificate)
    {
        return new Store($this->getThriftClientFactory(), $certificate, $type, $endpoint ?: $this->endpoint, $config ?: $this->config);
    }

    public function parseFilters()
    {

    }

}

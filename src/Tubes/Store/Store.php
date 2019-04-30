<?php namespace Steenbag\Tubes\Store;

use Steenbag\Tubes\Certificate\Certificate;
use Steenbag\Tubes\Contract\ApiKey;
use Steenbag\Tubes\Contract\Repository;
use Steenbag\Tubes\Factory\ShellClientFactory;

class Store
{

    protected $clientFactory;

    protected $certificate;

    protected $type;

    protected $endpoint;

    protected $config;

    public function __construct($clientFactory, Certificate $certificate, $type, $endpoint, Repository $config)
    {
        $this->clientFactory = $clientFactory;
        $this->certificate = $certificate;
        $this->type = $type;
        $this->endpoint = $endpoint;
        $this->config = $config;
    }

    /**
     * Pass calls through to an underling store instance.
     *
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $client = $this->getThriftClient($this->config->get('protocol'));

        return call_user_func_array([$client, $method], $args);
    }

    /**
     * Return an appropriate client instance.
     * @param string $protocol
     * @return
     */
    public function getThriftClient($protocol = 'binary')
    {
        return $this->getClientFactory()->createClient($this->type, $this->endpoint, $protocol, $this->certificate, true);
    }

    /**
     * Return the Client Factory
     *
     * @return string
     */
    public function getClientFactory()
    {
        if (null === $this->clientFactory) {
            $this->clientFactory = new ShellClientFactory($this->config);
        }

        return $this->clientFactory;
    }

}

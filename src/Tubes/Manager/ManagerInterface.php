<?php
/**
 * Created by PhpStorm.
 * User: ASteenbuck
 * Date: 9/22/15
 * Time: 1:11 PM
 */
namespace Steenbag\Tubes\Manager;

use Steenbag\Tubes\Contract\Authenticator;
use Steenbag\Tubes\Contract\CertStore;
use Steenbag\Tubes\Contract\Request;
use Steenbag\Tubes\WebService\ServiceInterface;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Protocol\TJSONProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Protocol\TProtocol;
use Thrift\Transport\TMemoryBuffer;

interface ManagerInterface
{

    /**
     * Resolve the requested service driver class.
     *
     * @param string $api
     * @return WebServiceInterface
     */
    public function getServiceDriver($api);

    /**
     * Generate an exception for an RPC call.
     *
     * @param ServiceInterface $driver
     * @param $method
     * @param $protocol
     * @param \Exception $exception
     * @return mixed
     */
    public function createExceptionBody(ServiceInterface $driver, $method, $protocol, \Exception $exception);

    /**
     * Get the response for a particular service request.
     *
     * @param $api
     * @param $method
     * @param string $protocol
     * @param string $requestBody
     * @return mixed
     * @internal param $driver
     */
    public function getServiceData($api, $method, $protocol = 'json', $requestBody);

    /**
     * Build a Thrift-compatible request body using the provided data from an HTTP request.
     *
     * @param $driver
     * @param $method
     * @param string $protocol
     * @param array $args
     * @return string
     * @internal param $api
     * @internal param $version
     */
    public function getRequestBody(ServiceInterface $driver, $method, $protocol = 'json', array $args = []);

    /**
     * Return the requested Thrift Protocol.
     *
     * @param $protocol
     * @param $buffer
     * @return TProtocol
     */
    public function getThriftProtocol($protocol, $buffer);

    /**
     * Return an associative array of all the bound service data.
     *
     * @return array
     */
    public function getAllServiceDefinitions();

    /**
     * Get the api key identified by the given slug.
     *
     * @param $slug
     * @return \Steenbag\Tubes\Keys\ApiKey
     */
    public function getServiceKey($slug);

    /**
     * Given the string API Key, retrieve the API Key model.
     *
     * @param $apiKey
     * @return mixed
     */
    public function getApiKey($apiKey);

    /**
     * Get the current Certificate Store.
     *
     * @return CertStore
     */
    public function getCertStore();

    /**
     * Set the current Certificate Store.
     *
     * @param CertStore $certStore
     * @return void
     */
    public function setCertStore(CertStore $certStore);

    /**
     * Get the service and method names from the request body.
     *
     * @param TProtocol $protocol
     * @return array
     */
    public function getRequestDataFromProtocol(TProtocol $protocol);

    /**
     * Get the information about the
     *
     * @param Request $request
     * @param $protocol
     * @return array
     */
    public function getRequestDataFromRequestBody(Request $request, $protocol);

    /**
     * Get a Thrift Protocol to use for input.
     *
     * @param \Steenbag\Tubes\Contract\Request $request
     * @param $protocol
     * @return TMultiplexedProtocol
     */
    public function getInputProtocol(\Steenbag\Tubes\Contract\Request $request, $protocol);

    /**
     * Get the authenticator instance.
     *
     * @return Authenticator
     */
    public function getAuthenticator();

}

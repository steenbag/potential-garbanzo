<?php namespace Steenbag\Tubes\Manager;

use Steenbag\Tubes\Contract\ApiKeyProvider;
use Steenbag\Tubes\Contract\Authenticator;
use Steenbag\Tubes\Contract\CertStore;
use Steenbag\Tubes\Contract\Container;
use Steenbag\Tubes\Contract\Repository;
use Steenbag\Tubes\Contract\Router;
use Steenbag\Tubes\Illuminate\Request;
use Steenbag\Tubes\Keys\Ardent\ApiKey;
use Steenbag\Tubes\WebService\BaseService;
use Steenbag\Tubes\WebService\ServiceInterface;
use Thrift\Factory\TStringFuncFactory;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Protocol\TJSONProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Protocol\TProtocol;
use Thrift\TMultiplexedProcessor;
use Thrift\Transport\TMemoryBuffer;
use Thrift\Type\TMessageType;

class ApiManager implements ManagerInterface
{

    protected $app;


    protected $config;

    protected $apiKeyProvider;

    protected $apis;

    protected $certStore;

    protected $services;

    protected $authenticator;

    public function __construct(Container $app, Repository $config, ApiKeyProvider $provider, CertStore $certStore, Authenticator $authenticator)
    {
        $this->app = $app;
        $this->config = $config;
        $this->apiKeyProvider = $provider;
        $this->certStore = $certStore;
        $this->services = [];
        $this->authenticator = $authenticator;
    }

    /**
     * Set the array of services.
     *
     * @param array $services
     * @return array
     */
    public function initServices(array $services)
    {
        return array_map([$this, 'addService'], array_keys($services), $services);
    }

    /**
     * @param $identifier
     * @param ServiceInterface $driver
     */
    public function addService($identifier, ServiceInterface $driver)
    {
        $this->services[$identifier] = [
            'key' => $identifier,
            'name' => $driver::getName(),
            'procedures' => $driver::listProcedures(),
            'driver' => get_class($driver),
            'instance' => $driver
        ];
    }

    /**
     * @return array
     */
    public function getAllServiceDefinitions()
    {
        return $this->services;
    }

    /**
     * Resolve the requested service driver class.
     *
     * @param $api
     * @return ServiceInterface
     */
    public function getServiceDriver($api)
    {
        $drivers = $this->services;
        if (isset($drivers[$api])) {
            $driver = $drivers[$api];
            if (is_null($driver)) {
                throw new \InvalidArgumentException("The {$api} service is not available.");
            }

            return $driver['instance'];
        }
    }

    /**
     * Build a Thrift-compatible request body using the provided data from an HTTP request.
     *
     * @param ServiceInterface $driver
     * @param $method
     * @param string $protocol
     * @param array $args
     * @return string
     * @internal param $api
     * @internal param $version
     * @throws \Thrift\Exception\TTransportException
     */
    public function getRequestBody(ServiceInterface $driver, $method, $protocol = 'json', array $args = [])
    {
        // Client In
        $clientInBuffer = new TMemoryBuffer();
        $clientInProtocol = $this->getThriftProtocol($protocol, $clientInBuffer);

        // Client Out
        $clientOutBuffer = new TMemoryBuffer();
        $clientOutProtocol = $this->getThriftProtocol($protocol, $clientOutBuffer);
        $clientClass = $driver::getThriftClient();
        $client = new $clientClass($clientInProtocol, $clientOutProtocol);

        // Fake the send request so that the Thrift client populates the buffer with
        // the generated request body.
        $methodName = 'send_' . $method;
        call_user_func_array([$client, $methodName], $args);

        return $clientOutBuffer->readAll(strlen($clientOutBuffer->getBuffer()));
    }

    /**
     * Process the response into a format we can actually use.
     *
     * @param ServiceInterface $driver
     * @param $method
     * @param $protocol
     * @param $responseData
     * @return mixed
     * @throws \Thrift\Exception\TTransportException
     */
    public function getResponseBody(ServiceInterface $driver, $method, $protocol, $responseData)
    {
        // Client In
        $clientInBuffer = new TMemoryBuffer();
        $clientInBuffer->write($responseData);
        $clientInProtocol = $this->getThriftProtocol($protocol, $clientInBuffer);

        // Client Out
        $clientOutBuffer = new TMemoryBuffer();
        $clientOutProtocol = $this->getThriftProtocol($protocol, $clientOutBuffer);
        $clientClass = $driver::getThriftClient();
        $client = new $clientClass($clientInProtocol, $clientOutProtocol);

        // Fake the receive method so that Thrift processes our raw response
        // Into the proper data format.
        $methodName = 'recv_' . $method;
        return call_user_func([$client, $methodName]);
    }

    /**
     * @param ServiceInterface $driver
     * @param $method
     * @param $protocol
     * @param \Exception $exception
     * @return mixed|string
     */
    public function createExceptionBody(ServiceInterface $driver, $method, $protocol, \Exception $exception)
    {
        $method = camelCase($method);
        try {
            $protocol = $protocol ?: $this->config->get('steenbag/tubes::default-protocol', 'json');

            $className = $driver::getThriftName() . '_' . $method . '_result';
            $result = new $className;
            $var = $this->inferExceptionVarName($className, $exception);
            if ($var) {
                $result->{$var} = $exception;
            }

            // Output
            $outBuffer = new TMemoryBuffer();
            $outProtocol = $this->getThriftProtocol($protocol, $outBuffer);

            $outProtocol->writeMessageBegin($method, TMessageType::REPLY, 0);
            $result->write($outProtocol);
            $outProtocol->writeMessageEnd();
            $outProtocol->getTransport()->flush();

            $data = $outBuffer->readAll(TStringFuncFactory::create()->strlen($outBuffer->getBuffer()));

            return $data;
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }

    /**
     * Given a driver class, and exception infer what the internal variable name is.
     *
     * @param $className
     * @param \Exception $exception
     * @return
     */
    protected function inferExceptionVarName($className, \Exception $exception)
    {
        $spec = $className::$_TSPEC;
        foreach ($spec as $i => $item) {
            if (isset($item['class']) && substr($item['class'], 1) === get_class($exception)) {
                return $item['var'];
            }
        }
    }

    /**
     * Return the requested Thrift Protocol.
     *
     * @param $protocol
     * @param $buffer
     * @return TProtocol
     */
    public function getThriftProtocol($protocol, $buffer)
    {
        switch ($protocol) {
            case 'json':
            case 'text':
                return new TJSONProtocol($buffer);
            case 'compact':
                return new TCompactProtocol($buffer);
            case 'binary':
                return new TBinaryProtocol($buffer);
        }

        throw new \InvalidArgumentException("The {$protocol} protocol is not supported at this time.");
    }

    /**
     * Respond to an RPC call.
     *
     * @param \Steenbag\Tubes\Contract\Request|Request $request
     * @param $protocol
     * @return mixed
     * @throws \Exception
     */
    public function getRpcServiceData(\Steenbag\Tubes\Contract\Request $request, $protocol)
    {
        try {
            // Input
            $inProtocol = $this->getInputProtocol($request, $protocol);

            // Output
            $outBuffer = new TMemoryBuffer();
            $outProtocol = $this->getThriftProtocol($protocol, $outBuffer);

            // Create a multiplexed processor.
            $multiProcessor = new TMultiplexedProcessor();
            $services = $this->getAllServiceDefinitions();
            // Register all of our handlers into the multiplexer.
            foreach ($services as $api => $driverDef) {
                $driver = $driverDef['driver'];
                echo $driver;
                $processorClass = "\\" . $driver::getThriftProcessor();
                if (class_exists($processorClass)) {
                    $multiProcessor->registerProcessor($driver::getThriftServiceName(), new $processorClass($driver));
                }
            }
            // Invoke the processor and get the result into the protocol.
            $multiProcessor->process($inProtocol, $outProtocol);

            return $outBuffer->readAll(TStringFuncFactory::create()->strlen($outBuffer->getBuffer()));
        } catch (\Exception $e) {
            \Log::error($e);
            throw $e;
        }
    }

    /**
     * Get the api key identified by the given slug.
     *
     * @param $slug
     * @return \Steenbag\Tubes\Contract\ApiKey
     */
    public function getServiceKey($slug)
    {
        return $this->apiKeyProvider->findCredentialBySlug($slug);
    }

    /**
     * Given the string API Key, retrieve the API Key model.
     *
     * @param $apiKey
     * @return ApiKey
     */
    public function getApiKey($apiKey)
    {
        return $this->apiKeyProvider->findCredentialByApiKey($apiKey);
    }

    /**
     * Get the current Certificate Store.
     *
     * @return CertStore
     */
    public function getCertStore()
    {
        return $this->certStore;
    }

    /**
     * Set the current Certificate Store.
     *
     * @param CertStore $certStore
     * @return void
     */
    public function setCertStore(CertStore $certStore)
    {
        $this->certStore = $certStore;
    }

    /**
     * Get the service and method names from the request body.
     *
     * @param TProtocol $protocol
     * @return array
     */
    public function getRequestDataFromProtocol(TProtocol $protocol)
    {
        $methodName = $returnType = $seqId = null;
        $protocol->readMessageBegin($methodName, $returnType, $seqId);

        list($serviceName, $methodName) = explode(TMultiplexedProtocol::SEPARATOR, $methodName, 2);

        return compact('serviceName', 'methodName');
    }

    /**
     * Get the information about the
     *
     * @param \Steenbag\Tubes\Contract\Request $request
     * @param $protocol
     * @return array
     */
    public function getRequestDataFromRequestBody(\Steenbag\Tubes\Contract\Request $request, $protocol)
    {
        $inProtocol = $this->getInputProtocol($request, $protocol);

        return $this->getRequestDataFromProtocol($inProtocol);
    }

    /**
     * Get a Thrift Protocol to use for input.
     *
     * @param \Steenbag\Tubes\Contract\Request $request
     * @param $protocol
     * @return TMultiplexedProtocol
     */
    public function getInputProtocol(\Steenbag\Tubes\Contract\Request $request, $protocol)
    {
        $requestBody = $request->getContent();

        $inBuffer = new TMemoryBuffer($requestBody);
        $inProtocol = $this->getThriftProtocol($protocol, $inBuffer);

        if ($request->header('X-Thrift-Multiplexed')) {
            $queryBuffer = new TMemoryBuffer($requestBody);
            $queryProtocol = $this->getThriftProtocol($protocol, $queryBuffer);

            $requestData = $this->getRequestDataFromProtocol($queryProtocol);

            return new TMultiplexedProtocol($inProtocol, $requestData['serviceName']);
        }

        return $inProtocol;
    }

    /**
     * Get the response for a particular service request.
     *
     * @param $api
     * @param string $method
     * @param string $protocol
     * @param string $requestBody
     * @return mixed
     * @internal param $driver
     */
    public function getServiceData($api, $method, $protocol = 'json', $requestBody)
    {
        $driver = $this->getServiceDriver($api);
        if ($driver->isProcedureCallable($method) === false) {
            throw new \InvalidArgumentException("The {$api}/{$method} procedure is not available.");
        }
        try {
            $protocol = $protocol ?: $this->config->get('steenbag/tubes::default-protocol', 'json');

            // Input
            $inBuffer = new TMemoryBuffer($requestBody);
            $inProtocol = $this->getThriftProtocol($protocol, $inBuffer);

            // Output
            $outBuffer = new TMemoryBuffer();
            $outProtocol = $this->getThriftProtocol($protocol, $outBuffer);

            $processorClass = $driver::getThriftProcessor();
            $processor = new $processorClass($driver);
            $processor->process($inProtocol, $outProtocol);

            $data = $outBuffer->readAll(TStringFuncFactory::create()->strlen($outBuffer->getBuffer()));

            return $data;
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }

    public function setAuthenticator(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    public function getAuthenticator()
    {
        return $this->authenticator;
    }
}

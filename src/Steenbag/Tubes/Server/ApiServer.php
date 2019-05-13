<?php namespace Steenbag\Tubes\Server;

use Steenbag\Tubes\Auth\AuthExceptionCodes;
use Steenbag\Tubes\Auth\RequestValidator;
use Steenbag\Tubes\Certificate\RsaZipCertStore;
use Steenbag\Tubes\Contract\ApiKeyProvider;
use Steenbag\Tubes\General\Auth\AuthorizationException;
use Steenbag\Tubes\General\Debug\ServerException;
use Steenbag\Tubes\General\Debug\StackTraceElement;
use Steenbag\Tubes\Manager\ApiManager;
use Steenbag\Tubes\NullImpl\Authenticator;
use Steenbag\Tubes\NullImpl\Container;
use Steenbag\Tubes\NullImpl\FileSystem;
use Steenbag\Tubes\NullImpl\Repository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiServer
{

    protected $request;

    protected $encoding;

    protected $container;

    protected $config;

    protected $keyProvider;

    protected $certStore;

    protected $manager;

    protected $debugMode = false;

    protected $fileSystem;

    public function __construct(array $config, ApiKeyProvider $keyProvider, \Steenbag\Tubes\Contract\Authenticator $authenticator)
    {
        $this->keyProvider = $keyProvider;
        $this->debugMode = isset($config['debug']) ? $config['debug'] : false;
        $options = getopt('', ['encoding:', 'uri:', 'method:', 'requestParams::', 'headers::', 'content::']);
        $encoding = isset($options['encoding']) ? $options['encoding'] : 'text';
        $requestUri = $options['uri'];
        $requestMethod = $options['method'];
        $requestHeaders = decodeCliParam($options['headers'], $encoding);

        if (array_key_exists('requestParams', $options)) {
            $requestParams = decodeCliParam($options['requestParams'], $encoding);
        } else {
            $requestParams = [];
        }

        $this->encoding = $encoding;

        $requestBody = decodeCliParam($options['content'], $encoding, $encoding !== 'text');
        $rawRequest = Request::create($requestUri, $requestMethod, $requestParams, [], [], [], $requestBody);
        $rawRequest->headers->add($requestHeaders);
        $this->request = new \Steenbag\Tubes\NullImpl\Request($rawRequest);

        $this->container = new Container;
        $this->config = new Repository($config);
        $this->keyProvider = $keyProvider;
        $fileSystem = new FileSystem;
        $this->fileSystem = $fileSystem;
        $this->certStore = new RsaZipCertStore($fileSystem);
        $this->certStore->setBasePath($this->config->get('steenbag/tubes::cert-store-path'));

        $this->manager = new ApiManager($this->container, $this->config, $this->keyProvider, $this->certStore, $authenticator);
        $this->manager->initServices($this->config->get('steenbag/tubes::rpc-services'));
    }

    /**
     * Run our RPC method and get a response.
     *
     * @return string|Response
     */
    public function run()
    {
        $serviceName = $methodName = '';
        $protocol = $this->inferProtocolFromRequest();
        try {
            extract($this->manager->getRequestDataFromRequestBody($this->request, $protocol));

            $validationResult = RequestValidator::validate($this->manager, $this->request, $serviceName, $methodName);

            $messageLookup = AuthExceptionCodes::$messages;
            $validationMessage = isset($messageLookup[$validationResult]) ? $messageLookup[$validationResult] : 'Invalid API Key';

            if ($validationResult !== true) {
                $driver = $this->manager->getServiceDriver($serviceName);
                $exception = new AuthorizationException(['why' => $validationMessage, 'code' => $validationResult]);
                $response = $this->manager->createExceptionBody($driver, $methodName, $protocol, $exception);

                return $this->negotiateResponse($response, $protocol, 403);
            }

            $result = $this->manager->getRpcServiceData($this->request, $protocol);

            $response = $this->negotiateResponse($result, $protocol);

            if ($this->request->header('Compress')) {
                if ($this->encoding === 'binary') {
                    echo $response->getContent();
                    $response->setContent(bin2hex(gzdeflate($response->getContent())));
                } else {
                    $response->setContent(base64_encode(gzdeflate($response->getContent())));
                }
            }

            return $response->getContent();
        } catch (\Exception $e) {
            $driver = $this->manager->getServiceDriver($serviceName);
            $thriftException = $this->createThriftException($e);
            $response = $this->manager->createExceptionBody($driver, $methodName, $protocol, $thriftException);

            return $this->negotiateResponse($response, $protocol, 200);
        }
    }

    /**
     * Create the response body.
     *
     * @param $content
     * @param string $protocol
     * @param int $status
     * @return Response
     */
    protected function negotiateResponse($content, $protocol = 'json', $status = 200)
    {
        $headers = [];
        switch ($protocol) {
            case 'json':
                $headers['Content-Type'] = 'application/json';
                break;
        }

        return new Response($content, $status, $headers);
    }

    /**
     * Determine the Thrift protocol to use.
     *
     * @return string|null
     */
    protected function inferProtocolFromRequest()
    {
        $defaultProtocol = $this->config->get('steenbag/tubes::default-protocol', 'json');

        return $this->request->header('Thrift-Transport-Encoding', $defaultProtocol);
    }

    /**
     * Create a new exception type that can be passed back to the client.
     *
     * @param \Exception $exception
     * @return ServerException
     */
    protected function createThriftException(\Exception $exception)
    {
        $message = $exception->getMessage();

        $exceptionArgs = [
            'code' => $exception->getCode(),
            'message' => $message
        ];

        // Add additional debug info if we're configured to do so.
        if ($this->debugMode) {
            $trace = [];
            foreach ($exception->getTrace() as $element) {
                try {
                    $args = [];
                    array_map('json_encode', isset($element['args']) ? $element['args'] : []);
                } catch (\Exception $e) {
                    $args = [];
                }
                $trace []= new StackTraceElement([
                    'file' => isset($element['file']) ? $element['file'] : null,
                    'line' => isset($element['line']) ? $element['line'] : null,
                    'class_ref' => isset($element['class']) ? $element['class'] : null,
                    'type' => isset($element['type']) ? $element['type'] : null,
                    'object' => json_encode(isset($element['object']) ? $element['object'] : null),
                    'function_ref' => isset($element['function']) ? $element['function'] : null,
                    'arguments' => $args
                ]);
            }
            $exceptionArgs = array_merge($exceptionArgs, [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $trace
            ]);
        }
        $thriftException = new ServerException($exceptionArgs);

        return $thriftException;
    }

    /**
     * Return the current request.
     *
     * @return \Steenbag\Tubes\NullImpl\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

}

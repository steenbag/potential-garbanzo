<?php namespace Steenbag\Tubes\Controllers;

use Config;
use Lang;

use MongoDB\Driver\Exception\ServerException;
use Steenbag\Tubes\Auth\AuthExceptionCodes;
use Steenbag\Tubes\Auth\RequestValidator;
use Steenbag\Tubes\General\Auth\AuthorizationException;
use Steenbag\Tubes\General\Auth\AuthRejectionCode;
use Steenbag\Tubes\General\Debug\StackTraceElement;
use Steenbag\Tubes\Illuminate\Request;
use Steenbag\Tubes\Manager\ApiManager;
use Illuminate\Support\Facades\Response;

class WebServiceController extends \BaseController
{

    protected $apiManager;

    public function __construct(ApiManager $manager)
    {
        $this->apiManager = $manager;
    }

    /**
     * Handle a call to an RPC method.
     *
     * @param \Illuminate\Http\Request $rawRequest
     * @param null $protocol
     * @return \Illuminate\Http\Response
     */
    public function handleRpc(\Illuminate\Http\Request $rawRequest, $protocol = null)
    {
        $serviceName = $methodName = '';
        try {
            // We have to find a new way to perform the signing since this information is no longer included in the URI.
            $protocol = $this->inferProtocolFromRequest($rawRequest, $protocol);
            $request = new Request($rawRequest);
            extract($this->apiManager->getRequestDataFromRequestBody($request, $protocol));
            $startValidate = microtime(true);
            $result = RequestValidator::validate($this->apiManager, $request, $serviceName, $methodName);
            $messageLookup = AuthExceptionCodes::$messages;
            $message = isset($messageLookup[$result]) ? $messageLookup[$result] : 'Invalid API Key';
            \Log::info('Web API Request: Identified By: ' . $request->header('X-Thrift-Auth') . ', Result: ' . ($result === true ? 'OK' : 'FAILED (' . $message . ')'));
            $endValidate = microtime(true);
            if ($result !== true) {
                $driver = $this->apiManager->getServiceDriver($serviceName);
                $response = $this->apiManager->createExceptionBody($driver, $methodName, $protocol, new AuthorizationException(['why' => $message, 'code' => $result]));

                return $this->negotiateResponse($response, $protocol, 403);
            }
            \Log::debug('Auth Time: ' . ($endValidate - $startValidate) . 's');

            $startData = microtime(true);
            $data = $this->apiManager->getRpcServiceData($request, $protocol);
            $endData = microtime(true);
            \Log::debug('Data Time: ' . ($endData - $startData) . 's');

            return $this->negotiateResponse($data);
        } catch (\Exception $e) {
            $driver = $this->apiManager->getServiceDriver($serviceName);
            $thriftException = $this->createThriftException($e);
            $response = $this->apiManager->createExceptionBody($driver, $methodName, $protocol, $thriftException);

            return $this->negotiateResponse($response, $protocol, 200);
        }
    }

    /**
     * Handle a call to a REST method.
     *
     * @param \Illuminate\Http\Request $rawRequest
     * @param $api
     * @param $method
     * @return \Illuminate\Http\Response
     * @todo Maybe rename this to handleRest
     */
    public function handleRest(\Illuminate\Http\Request $rawRequest, $api, $method)
    {
        $protocol = $this->inferProtocolFromRequest($rawRequest, $method);
        $request = new Request($rawRequest);
        $requestContent = $request->getContent();

        $prefix = \Config::get('steenbag/tubes::base-service-url', 'web-services');
        if ($request->is("{$prefix}/*")) {
            $result = RequestValidator::validate($request, $api, $method);
            //\Log::info(gettype($result));
            $messageLookup = [
                AuthRejectionCode::BAD_GRANT => 'Insufficient API permissions',
                AuthRejectionCode::EXPIRED => 'API Request Expired',
                AuthRejectionCode::BAD_KEY => 'Invalid API Key',
                AuthRejectionCode::INVALID_SIGNATURE => 'Invalid Signature Format',
                AuthRejectionCode::DISABLED_KEY => 'API Key is Disabled'
            ];
            $message = isset($messageLookup[$result]) ? $messageLookup[$result] : 'Invalid API Key';
            \Log::info('Web API Request: ' . $request->path() . ', Identified By: ' . $request->header('Authorization') . ', Result: ' . ($result === true ? 'OK' : 'FAILED'));
            if ($result !== true) {
                $response = $this->apiManager->createExceptionBody($api, $method, $protocol, new AuthorizationException(['why' => $message, 'code' => $result]));

                return $this->negotiateResponse($response, $protocol, 403);
            }
        }

        $data = $this->apiManager->getServiceData($api, $method, $protocol, $requestContent);

        return $this->negotiateResponse($data);
    }

    /**
     * Create the Response object.
     *
     * @param $response
     * @param string $protocol
     * @param int $status
     * @return mixed
     */
    protected function negotiateResponse($response, $protocol = 'json', $status = 200)
    {
        $headers = [
            //'Content-Type' => 'application/x-thrift'
        ];
        switch ($protocol) {
            case 'json':
                $headers['Content-Type'] = 'application/json';
                break;
        }

        return Response::make($response, $status, $headers);
    }

    /**
     * Determine the transport protocol to use based on the request.
     *
     * @param \Illuminate\Http\Request $request
     * @param $method
     * @return string
     */
    protected function inferProtocolFromRequest(\Illuminate\Http\Request $request, &$method)
    {
        $protocol = $request->header('Thrift-Transport-Encoding');

        if (is_null($protocol) && ends_with($method, '.json')) {
            $method = str_replace('.json', '', $method);
            return 'json';
        }

        if (is_null($protocol) && ends_with($method, '.bin')) {
            $method = str_replace('.bin', '', $method);
            return 'binary';
        }

        if (is_null($protocol) && ends_with($method, '.cmp')) {
            $method = str_replace('.cmp', '', $method);
            return 'compact';
        }

        if (is_null($protocol)) {
            $protocol = \Config::Get('steenbag/tubes::default-protocol', 'json');
        }

        if(strpos($method, '.')) {
            $methodParts = explode('.', $method, -1);
            $method = $methodParts[0];
        }

        return $protocol;
    }

    /**
     * Create a new exception type that can be passed back to the client.
     *
     * @param \Exception $exception
     * @return ServerException
     */
    protected function createThriftException(\Exception $exception)
    {
        if (Config::get('app.user_friendly_errors', true) === false) {
            $message = $exception->getMessage();
        } else {
            if (Lang::has('errors.' . $exception->getCode())) {
                $message = Lang::get('errors.' . $exception->getCode());
            } elseif (Lang::has('errors.0')) {
                $message = Lang::get('errors.0');
            } else {
                $message = 'An unknown error occurred.';
            }
        }
        $exceptionArgs = [
            'code' => $exception->getCode(),
            'message' => $message
        ];

        // Add additional, sensitive info if we are in a safe environment to do so.
        if (Config::get('app.debug', false)) {
            $trace = [];
            foreach ($exception->getTrace() as $element) {
                try {
                    $args = [];array_map('json_encode', array_get($element, 'args', []));
                } catch (\Exception $e) {
                    \Log::error($e);
                    $args = [];
                }
                $trace []= new StackTraceElement([
                    'file' => array_get($element, 'file'),
                    'line' => array_get($element, 'line'),
                    'class_ref' => array_get($element, 'class'),
                    'type' => array_get($element, 'type'),
                    'object' => json_encode(array_get($element, 'object')),
                    'function_ref' => array_get($element, 'function'),
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

}

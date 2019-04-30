<?php namespace Steenbag\Tubes\Auth;

use Steenbag\Tubes\General\Auth\AuthRejectionCode;
use Steenbag\Tubes\Keys\Ardent\ApiKey;
use Steenbag\Tubes\Keys\Ardent\ApiKeyProvider;
use Steenbag\Tubes\Manager\ApiManager;
use Steenbag\Tubes\Contract\Request;
use Steenbag\Tubes\Token\Builder;
use Steenbag\Tubes\Token\Claim\Claim;
use Steenbag\Tubes\Token\Claim\ContainsEqualsTo;
use Steenbag\Tubes\Token\Claim\Factory;
use Steenbag\Tubes\Token\ValidationData;

class RequestValidator
{

    protected $apiManager;

    protected $request;

    protected $api;

    protected $method;

    public function __construct(ApiManager $apiManager, Request $request, $api, $method)
    {
        $this->apiManager = $apiManager;
        $this->request = $request;
        $this->api = $api;
        $this->method = $method;
    }

    /**
     * @return bool|int
     */
    public function performValidation()
    {
        $request = $this->request;

        try {
            $authHeader = $request->header('X-Thrift-Auth');
            $dateHeader = $request->header('Request-Date');
            if (empty($authHeader) || empty($dateHeader)) {
                return false;
            }
            $bearerLocation = strpos($authHeader, 'Bearer');
            $sharedKeyLocation = strpos($authHeader, 'SharedKey');
            if (is_bool($bearerLocation) === false && $bearerLocation >= 0) {
                $result = $this->validateToken($authHeader, $dateHeader);
            } elseif (is_bool($sharedKeyLocation) === false && $sharedKeyLocation >= 0) {
                $result = $this->validateAssymetric($authHeader, $dateHeader);
            } else {
                return AuthRejectionCode::INVALID_SIGNATURE;
            }

            if ($result === false) {
                $result = AuthRejectionCode::BAD_KEY;
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error($e);
            return false;
        }
    }

    /**
     * Validate a request signed with assymetric certificates.
     *
     * @param $authHeader
     * @param $dateHeader
     * @return bool|int
     */
    protected function validateAssymetric($authHeader, $dateHeader)
    {
        $request = $this->request;
        $apiManager = $this->apiManager;

        $pattern = '/SharedKey (.*) (.*)/';
        preg_match($pattern, $authHeader, $matches);

        if (count($matches) !== 3) {
            return AuthRejectionCode::INVALID_SIGNATURE;
        }

        $strApiKey = $matches[1];
        $requestSignature = $matches[2];
        $requestUri = $request->path();

        $apiKey = $apiManager->getApiKey($strApiKey);

        $validApiKey = $this->validateApiKey($apiKey, $dateHeader);
        if ($validApiKey !== true) {
            return $validApiKey;
        }

        $requestContent = $request->getContent();
        $_headers = [];
        $signHeaders = ['request-date', 'compress', 'thrift-transport-encoding', 'content-length'];
        foreach ($request->header() as $key => $value) {
            if (in_array($key, $signHeaders)) {
                $_headers[$key] = $value[0];
            }
        }
        $signer = new RequestSigner();
        $certificate = $apiManager->getCertStore()->getCertificate($apiKey);
        $result = $signer->verify($certificate, $requestSignature, $requestUri, strlen($requestContent), md5($requestContent), $request->header('Request-Date'));

        if ($result === false) {
            return AuthRejectionCode::BAD_KEY;
        } else {
            $authenticator = $apiManager->getAuthenticator();

            if ($authenticator->authenticate($request, $apiKey) === false) {
                return AuthRejectionCode::INVALID_LOGIN;
            }
        }

        return $result;
    }

    /**
     * Validate a previously-generated bearer token.
     *
     * @param $authHeader
     * @param $dateHeader
     * @return bool|int
     */
    protected function validateToken($authHeader, $dateHeader)
    {
        $apiManager = $this->apiManager;
        $api = $this->api;
        $method = $this->method;

        $pattern = '/Bearer (.*)/';
        preg_match($pattern, $authHeader, $matches);

        if (count($matches) !== 2) {
            return AuthRejectionCode::INVALID_SIGNATURE;
        }

        $jwt = $matches[1];

        $additionalClaims = [
            'grt' => function ($name, $value) {
                return new ContainsEqualsTo($name, $value);
            }
        ];
        $factory = new Factory($additionalClaims);
        $verifier = Builder::load($jwt, $factory);
        $strApiKey = $verifier->getClaim(Claim::KEY_ID)->getValue();

        $apiKey = $apiManager->getApiKey($strApiKey);

        $validApiKey = $this->validateApiKey($apiKey, $dateHeader);
        if ($validApiKey !== true) {
            return $validApiKey;
        }

        $certificate = $apiManager->getCertStore()->getCertificate($apiKey);

        $userIdClaim = $verifier->getClaim(Claim::USER_ID);
        if (!isset($userIdClaim)) {
            return AuthRejectionCode::INVALID_LOGIN;
        }

        $publicKey = $certificate->getPublicKey();
        $validator = new ValidationData;
        $validator->setId('thrift_api_access');
        $validator->setKeyId($apiKey->api_key);
        $validator->setUserId(\Sentry::getUser()->getKey());
        $validator->set('grt', "{$api}::{$method}");

        return $verifier->verify($publicKey, $validator);
    }

    /**
     * Determine if our API Key is valid or not.
     *
     * @param ApiKey $apiKey
     * @param $dateHeader
     * @return bool|int
     */
    protected function validateApiKey(ApiKey $apiKey, $dateHeader)
    {
        $api = $this->api;
        $method = $this->method;
        $requestDate = \Carbon::parse($dateHeader);
        // Requests cannot be more than 30 seconds old.
        // This helps to mitigate replay attacks.
        if ($requestDate->diffInSeconds(\Carbon::now(), true) > 30) {
            return AuthRejectionCode::EXPIRED;
        }

        if (!$apiKey) {
            return AuthRejectionCode::BAD_KEY;
        }

        if ( ! $apiKey->isValid()) {
            return AuthRejectionCode::DISABLED_KEY;
        }

        if (!$this->validateGrant($apiKey, $api, $method)) {
            return AuthRejectionCode::BAD_GRANT;
        }

        return true;
    }

    /**
     * A static shortcut to validating our request.
     *
     * @param ApiManager $apiManager
     * @param Request $request
     * @param $api
     * @param $method
     * @return mixed
     */
    public static function validate(ApiManager $apiManager, Request $request, $api, $method)
    {
        $validator = new static($apiManager, $request, $api, $method);

        return $validator->performValidation();
    }

    /**
     * Get the private key from the database based on the public key.
     *
     * @param string $publicKey
     * @return string
     */
    protected static function getPrivateKey($publicKey)
    {
        $provider = new ApiKeyProvider();
        $credential = $provider->findCredentialByApiKey($publicKey);

        return $credential;
    }

    /**
     * Returns true if the passed key can access the requested service.
     *
     * @param ApiKey $key
     * @param $api
     * @param $method
     * @return boolean
     */
    protected function validateGrant(ApiKey $key, $api, $method)
    {
        return $key->isValidGrant($api, $method);
    }

}

<?php namespace Steenbag\Tubes\NullImpl;

use Steenbag\Tubes\Contract\ApiKey;
use Steenbag\Tubes\Contract\Request;

/**
 * The null implementation library allows for a very basic implementation of
 * the contract without relying on Laravel. Only internal types are used.
 * As such, functionality is limited only to what is defined in the contract.
 *
 * Class Container
 * @package Steenbag\Tubes\NullImpl
 */
class Authenticator implements \Steenbag\Tubes\Contract\Authenticator
{
    /**
     * Login the appropriate user based on the request
     *
     * @param  Request $request
     * @param ApiKey $apiKey
     * @return boolean
     */
    public function authenticate(Request $request, ApiKey $apiKey)
    {
        return true;
    }
}

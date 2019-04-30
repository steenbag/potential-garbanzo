<?php namespace Steenbag\Tubes\Contract;

interface Authenticator
{

    /**
     * Login the appropriate user based on the request
     *
     * @param Request $request
     * @param ApiKey $apiKey
     * @return bool
     */
    public function authenticate(Request $request, ApiKey $apiKey);

}

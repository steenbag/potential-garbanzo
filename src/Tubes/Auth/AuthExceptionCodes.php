<?php namespace Steenbag\Tubes\Auth;

use Steenbag\Tubes\General\Auth\AuthRejectionCode;

final class AuthExceptionCodes
{

    public static $messages = [
        AuthRejectionCode::BAD_GRANT => 'Insufficient API permissions',
        AuthRejectionCode::EXPIRED => 'API Request Expired',
        AuthRejectionCode::BAD_KEY => 'Invalid API Key',
        AuthRejectionCode::INVALID_SIGNATURE => 'Invalid Signature Format',
        AuthRejectionCode::DISABLED_KEY => 'API Key is Disabled',
        AuthRejectionCode::INVALID_LOGIN => 'Unable to login API User'
    ];

}

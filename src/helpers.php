<?php

if (!function_exists('decodeCliParam')) {

    /*
    |--------------------------------------------------------------------------
    | Read request parameters from the command line and set them in the request
    |--------------------------------------------------------------------------
    |
    | Here we will load this Illuminate application. We will keep this in a
    | separate location so we can isolate the creation of an application
    | from the actual running of the application with a given request.
    |
     */
    function decodeCliParam($strParam, $encoding, $jsonDecode = true)
    {
        switch ($encoding) {
            case 'binary':
                return binaryDecodeCliParam($strParam, $jsonDecode);
            case 'text':
            default:
                return textDecodeCliParam($strParam, $jsonDecode);
        }
    }

}

if (! function_exists('textDecodeCliParam')) {

    function textDecodeCliParam($strParam, $jsonDecode = true)
    {
        if (empty($strParam)) {
            return null;
        }
        $jsonParam = base64_decode($strParam);
        if ($jsonParam === false) {
            printf('param must be a valid base64 string: %s', $strParam);
            return false;
        }
        if (!$jsonDecode) {
            return $jsonParam;
        }
        $objParam = json_decode($jsonParam, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            printf('param must be a valid json string: %s', $jsonParam);
            return false;
        }
        return $objParam;
    }

}

if (! function_exists('binaryDecodeCliParam')) {

    function binaryDecodeCliParam($strParam, $jsonDecode = true)
    {
        if (empty($strParam)) {
            return null;
        }
        $decodeParam = hex2bin(base64_decode($strParam));
        if ($decodeParam === false) {
            printf('param must be a valid binary string: %s', $strParam);
            return false;
        }

        if ($jsonDecode && strpos($decodeParam, '{') === 0 || strpos($decodeParam, '[') === 0) {
            $decodeParam = json_decode($decodeParam, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                printf('param must be a valid json string: %s', $decodeParam);
                return false;
            }
        }

        return $decodeParam;
    }

}

if (! function_exists('snake_case')) {

    function snake_case($str, $delim = '_')
    {
        return \Steenbag\Tubes\Support\StringHelper::snake_case($str, $delim);
    }

}

if (! function_exists('camelCase')) {

    function camelCase($str)
    {
        return \Steenbag\Tubes\Support\StringHelper::camelCase($str);
    }

}

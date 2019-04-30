<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Base RPC Service Url
    |--------------------------------------------------------------------------
    |
    | This defines the base route that will contain all RPC web services.
    |
     */
    'base-rpc-service-url' => 'api/rpc',

    /*
    |--------------------------------------------------------------------------
    | Default Protocol
    |--------------------------------------------------------------------------
    |
    | This sets the default protocol to use if it is not overridden.
    | Valid values are json, binary or compact.
    |
     */
    'default-rpc-protocol' => 'json',

    /*
    |--------------------------------------------------------------------------
    | Key Storage Path
    |--------------------------------------------------------------------------
    |
    | Determines where in the file system the keys are stored.
    |
     */
    'key-storage-path' => storage_path('api-keys/'),

    /*
    |--------------------------------------------------------------------------
    | RPC Service Definition
    |--------------------------------------------------------------------------
    |
    | The following config key creates the RPC web services in the application.
    | Use the format 'route_prefix' => new DriverClass()
    |
     */
    'rpc-services' => [

    ]
];

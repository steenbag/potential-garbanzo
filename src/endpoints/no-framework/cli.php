<?php

require_once __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('America/New_York');

$config = [
    'steenbag/tubes::rpc-services' => [
        // new My\Api\Provider\Class()
    ]
];

$auth = new Authenticator();

$app = new Steenbag\Tubes\Server\ApiServer($config, new Steenbag\Tubes\Keys\PhpActiveRecord\ApiKeyProvider, $auth);

echo($app->run());
exit(0);

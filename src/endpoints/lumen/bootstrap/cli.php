<?php

/** @var \Laravel\Lumen\Application $app */
$app = require __DIR__ . '/app.php';

$options = getopt('', ['encoding:', 'uri:', 'method:', 'requestParams::', 'headers::', 'content::']);
$encoding = isset($options['encoding']) ? $options['encoding'] : 'text';
$uri = $options['uri'];
$method = $options['method'];
$headers = decodeCliParam($options['headers'], $encoding);

if (array_key_exists('requestParams', $options)) {
    $requestParams = decodeCliParam($options['requestParams'], $encoding);
} else {
    $requestParams = [];
}

$content = decodeCliParam($options['content'], $encoding, $encoding === 'text');

$request = \Illuminate\Http\Request::create($uri, $method, $requestParams, [], [], [], $content);
if (! empty($headers)) {
    $request->headers->add($headers);
}
$app['request'] = $request;

$app->after(function (\Symfony\Component\HttpFoundation\Request $request, \Symfony\Component\HttpFoundation\Response $response) use ($encoding) {
    if ($request->headers['Compress']) {
        if ($encoding === 'binary') {
            $newContent = bin2hex(bzcompress($response->getContent()));
        } else {
            $newContent = base64_encode(bzcompress($response->getContent()));
        }
        $response->setContent($newContent);
    }
});

return $app;

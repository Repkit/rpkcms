<?php

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var \Zend\Expressive\Application $app */
$app = $container->get('Zend\Expressive\Application');

// ###---- test begin
$pathMiddleware = function (
    Psr\Http\Message\ServerRequestInterface $request,
    Psr\Http\Message\ResponseInterface $response,
    callable $next
) {
    $uri  = $request->getUri();
    $path = $uri->getPath();

    $response->getBody()->write('You visited ' . $path);
    return $next($request, $response->withHeader('X-Path', $path));
};

$app->get('/admin', $pathMiddleware);
// ###---- test end

$app->run();

//###---- benchmark begin
$size = memory_get_peak_usage(true);
$unit=array('b','kb','mb','gb','tb','pb');
echo '<hr>',@round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
//###---- benchmark end
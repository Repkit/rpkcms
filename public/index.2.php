<?php

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$app = Zend\Expressive\AppFactory::create();

$app->get('/admin', function ($request, $response, $next) {
    // $response->getBody()->write('Hello, world!');
    // return $response;
    $uri  = $request->getUri();
    $path = $uri->getPath();
    
    $response->getBody()->write('You visited ' . $path);
    return $next($request, $response->withHeader('X-Path', $path));
});

$app->pipeRoutingMiddleware();
$app->pipeDispatchMiddleware();
$app->run();


//benchmark begin
$size = memory_get_peak_usage(true);
$unit=array('b','kb','mb','gb','tb','pb');
echo '<hr>',@round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
//benchmark end
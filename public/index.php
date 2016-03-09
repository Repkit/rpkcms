<?php
$ts = microtime(true); $ts1 = microtime();

session_start();

chdir(dirname(__DIR__));
require 'vendor/autoload.php';
$container = require 'config/container.php';
$app = $container->get('Zend\Expressive\Application');

// ###---- test begin
/*$app->get('/admin', function ($request, $response, $next) {
    // $response->getBody()->write('Hello, world!');
    // return $response;
    $uri  = $request->getUri();
    $path = $uri->getPath();
    
    $response->getBody()->write('You visited ' . $path);
    return $next($request, $response->withHeader('X-Path', $path));
});*/
// ###---- test end
$app->run();


//benchmark begin
$size = memory_get_peak_usage(true); $size1 = memory_get_peak_usage();
$t = microtime(true) - $ts; $t1 = microtime() - $ts1;
$unit=array('b','kb','mb','gb','tb','pb');
$m = @round($size/pow(1024,($i=floor(log($size,1024)))),2);
$m1 = @round($size1/pow(1024,($i)),2);
$u = $unit[$i];
($t>0.1 || $m>2)?$c='red':$c='green';
// echo '<hr><div style="background:',$c,'; color:white;">',$m,'(',$m1,')',$u,' -- ', $t,'(',$t1,')s</div>';
//benchmark end
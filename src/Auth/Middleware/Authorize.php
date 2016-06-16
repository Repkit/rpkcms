<?php

namespace Auth\Middleware;

use Zend\Expressive\Router\RouterInterface;

class Authorize
{
    private $config;

    /**
     * Constructor
     *
     * @param array $config Configuration for the Opauth instance
     */
    public function __construct(RouterInterface $Router, array $Config)
    {
        $this->router   = $Router;
        $this->config   = $Config;
    }

    public function __invoke($req, $res, $next)
    {
        $match = $this->router->match($req);
        // var_dump($match);exit(__FILE__.'::'.__LINE__);
        
        // authorize middleware
        $middleware = $match->getMatchedMiddleware();
        if(!empty($this->config['authorize']['middleware'][$middleware])){
            $roles = $this->config['authorize']['middleware'][$middleware];
            // var_dump($roles);exit(__FILE__.'::'.__LINE__);
            if(empty(($user = $_SESSION['auth']['user'])) || !in_array($user['role'],$roles)){
                return $next($req, $res->withStatus(403), 'This is a restricted area!');
            }
        }
        
        // route name authorization
        $route = $match->getMatchedRouteName();
        if(!empty($this->config['authorize']['route'][$route])){
            $roles = $this->config['authorize']['route'][$route];
            if(empty(($user = $_SESSION['auth']['user'])) || $user['info']['email'] !== 'repkit@gmail.com'){
                return $next($req, $res->withStatus(403), 'This is a restricted area!');
            }
        }
        
        return $next($req, $res);
    }
}
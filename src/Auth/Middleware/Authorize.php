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
            $user = null;
            if(!empty($_SESSION['auth']['user'])){
                $user = $_SESSION['auth']['user'];
            }
            if(!is_array($user) || empty($user['role']) || !in_array($user['role'],$roles)){
                return $next($req, $res->withStatus(403), 'This is a restricted area!');
            }
        }
        
        // route name authorization
        $route = $match->getMatchedRouteName();
        if(!empty($this->config['authorize']['route'][$route])){
            $roles = $this->config['authorize']['route'][$route];
            $user = null;
            if(!empty($_SESSION['auth']['user'])){
                $user = $_SESSION['auth']['user'];
            }
            if(!is_array($user) || !isset($user['info']['email']) || $user['info']['email'] !== 'repkit@gmail.com'){
                return $next($req, $res->withStatus(403), 'This is a restricted area!');
            }
        }
        
        return $next($req, $res);
    }
}
<?php
namespace Auth\Action;

use Opauth;

class AuthAction
{
    private $config;

    /**
     * Constructor
     *
     * @param array $config Configuration for the Opauth instance
     */
    public function __construct(array $authConfig)
    {
        $this->config   = $authConfig;
    }

    public function __invoke($req, $res, $next)
    {
        session_start();
        // var_dump($req->getQueryParams());exit();
        if (isset($req->getQueryParams()['redirect'])) {
            $_SESSION['auth']['redirect'] = $req->getQueryParams()['redirect'];
        }else{
            $_SESSION['auth']['redirect'] = $req->getHeaders()['referer'][0];
        }
        
        $auth = new Opauth($this->config);
        return $res;
    }
}

<?php
namespace Auth\Action;

use Opauth;
use Zend\Diactoros\Uri;

class AuthCallbackAction
{
    private $config;

    public function __construct(array $authConfig)
    {
        $this->config  = $authConfig;
    }

    public function __invoke($req, $res, $next)
    {
        $auth         = new Opauth($this->config, false);
        $authResponse = null;
        
        switch($auth->env['callback_transport']) {
            case 'session':
                if(!session_id()) {
					session_start();
				}
    			$authResponse = $_SESSION['opauth'];
        		unset($_SESSION['opauth']);
        		$redirect = $_SESSION['auth']['redirect'];
        		unset($_SESSION['auth']['redirect']);
                break;
            case 'post':
                $authResponse = unserialize(base64_decode($req->getParsedBody()['opauth']));
                $redirect = unserialize(base64_decode($req->getParsedBody()['auth']['redirect']));
                break;
            case 'get':
                $authResponse = unserialize(base64_decode($req->getQueryParams()['opauth']));
                $redirect = unserialize(base64_decode($req->getQueryParams()['auth']['redirect']));
                break;
            default:
                return $next($req, $res->withStatus(400), 'Invalid request');
                break;
        }

        if (array_key_exists('error', $authResponse)) {
            // var_dump($authResponse['error']);echo $authResponse['error']['raw']['headers'];exit(__CLASS__.__LINE__);
            return $next($req, $res->withStatus(403), 'Error authenticating');
        }

        if (empty($authResponse['auth'])
            || empty($authResponse['timestamp'])
            || empty($authResponse['signature'])
            || empty($authResponse['auth']['provider'])
            || empty($authResponse['auth']['uid'])
        ) {
            return $next($req, $res->withStatus(403), 'Invalid authentication response');
        }
        
        if ($auth->env['callback_transport'] !== 'session'
            && ! $auth->validate(
                sha1(print_r($authResponse['auth'], true)),
                $authResponse['timestamp'],
                $response['signature'],
                $reason
            )
        ) {
            return $next($req, $res->withStatus(403), 'Invalid authentication response');
        }
        
        //authentication success
        // var_dump($authResponse);exit(__CLASS__.__LINE__);
        $_SESSION['auth']['user'] = $authResponse['auth'];

        $uri = $req->getUri()->withPath('/');
        
        if ($redirect) {
            $uri = new Uri($redirect);
        }

        return $res
            ->withStatus(302)
            ->withHeader('Location', (string) $uri);
    }
}

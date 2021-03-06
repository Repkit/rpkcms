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
        
        switch($this->config['callback_transport']) {
            case 'session':
                if(!session_id()) {
					session_start();
				}
    			 if (isset($req->getQueryParams()['redirect'])) {
                    $_SESSION['auth']['redirect'] = $req->getQueryParams()['redirect'];
                }else{
                    $headers = $req->getHeaders();
                    if(!empty($headers['referer'][0])){
                        $_SESSION['auth']['redirect'] = $req->getHeaders()['referer'][0];
                    }
                }
                break;
            case 'post':
                // TODO [NEW FEATURE]: implement
                break;
            case 'get':
                // TODO [NEW FEATURE]: implement
                break;
            default:
                return $next($req, $res->withStatus(400), 'Invalid request');
                break;
        }
        
        $auth = new Opauth($this->config);
        
        // if seems this code is never executed!!!
        /*switch($auth->env['callback_transport']) {
            case 'session':
                if(!session_id()) {
					session_start();
				}
    			 if (isset($req->getQueryParams()['redirect'])) {
                    $_SESSION['auth']['redirect'] = $req->getQueryParams()['redirect'];
                }else{
                    $_SESSION['auth']['redirect'] = $req->getHeaders()['referer'][0];
                }
                break;
            case 'post':
                // TODO [NEW FEATURE]: implement
                break;
            case 'get':
                // TODO [NEW FEATURE]: implement
                break;
            default:
                return $next($req, $res->withStatus(400), 'Invalid request');
                break;
        }*/
        
        return $res;
    }
}

<?php

namespace Page\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait DispacherTrait
{
    public function __invoke(
        ServerRequestInterface $Request,
        ResponseInterface $Response,
        callable $Next = null
    ) {
        $action = $Request->getAttribute('action', 'index') . 'Action';

        if (! method_exists($this, $action)) {
            return $Response->withStatus(404);
        }

        return $this->$action($Request, $Response, $Next);
        
        // never used but keeped here like a though
        /*$response = $this->$action($Request, $Response, $Next);
        
        if(!$response instanceof ResponseInterface){
            
            // trigger event here 
            PluginChain::trigger(__FUNCTION__, ['template'=> 'app::home-page', 'data' => $data]);
            
            $template = !empty($response['template']) ? $response['template'] : "page::$action";
            $data = !empty($response['data']) ? $response['data'] : $response;
            $response = new HtmlResponse($this->template->render($response['template'], $response['data']));
            $response = $Next($Request, $response);
            
        }
        
        return $response;*/
    }
}
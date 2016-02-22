<?php

namespace Page\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

trait PaginationDataFromRequestTrait
{
    /**
     * @param \Psr\Http\Message\RequestInterface $req
     * @return int
     */
    private function getPaginationDataFromRequest($Request)
    {
        $data = [];
        
        $size = isset($Request->getQueryParams()['size']) ? $Request->getQueryParams()['size'] : 10;
        $page = isset($Request->getQueryParams()['page']) ? $Request->getQueryParams()['page'] : false;
        
        if(false === $page){
            $from = isset($Request->getQueryParams()['from']) ? $Request->getQueryParams()['from'] : false;
            if(false === $from){
                $page = 1;
            }else{
                $from = (int) $from;
                if(0 == $from){
                    $page = 1;
                }else{
                    $page = ($from/$size) + 1;
                }
            }
        }else{
            $page = (int) $page;
            if($page < 1){
                $page = 1;
            }
        }
            
        $data['size'] = $size;
        $data['page'] = $page;
        
        return $data;
    }
}
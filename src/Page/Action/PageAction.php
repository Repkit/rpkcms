<?php

namespace Page\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Page\Storage\StorageInterface;
use Page\Storage\StorageException;

class PageAction
{
    private $router;

    private $template;
    
    private $storage;
    
    use DispacherTrait;

    public function __construct(StorageInterface $Storage, Router\RouterInterface $Router
        , Template\TemplateRendererInterface $Template = null)
    {
        $this->storage  = $Storage;
        $this->router   = $Router;
        $this->template = $Template;
    }
    
    public function indexAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $path = $Request->getOriginalRequest()->getUri()->getPath();
        $pagdata = $this->getPaginationDataFromRequest($Request);
        
        $pages = $this->storage->fetchAll();
        $cnt = count($pages);
        
        // If the requested page is later than the last, redirect to the last
        /*if ($cnt && $pagdata['page'] > $cnt) {
            return $Response
                ->withStatus(302)
                ->withHeader('Location', sprintf('%s?page=%d', $path, $cnt));
        }*/
        
        $pages->setItemCountPerPage($pagdata['size']);
        $pages->setCurrentPageNumber($pagdata['page']);

        // $data['pages'] = iterator_to_array($pages->getItemsByPage($page));
        $data['pages'] = iterator_to_array($pages->getCurrentItems());
        
        return new JsonResponse($data);
        // return new HtmlResponse($this->template->render('page::list', $data));
        
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        return new HtmlResponse($this->template->render('page::add', $data));
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        $data['id'] = $id;
        return new HtmlResponse($this->template->render('page::edit', $data));
    }
    
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

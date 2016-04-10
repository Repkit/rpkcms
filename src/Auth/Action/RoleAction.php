<?php

namespace Auth\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Auth\Storage\StorageInterface;
use Auth\Storage\StorageException;

class RoleAction
{
    private $router;

    private $template;
    
    private $storage;
    
    use DispacherTrait;
    use PaginationDataFromRequestTrait;

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
        
        $entities = $this->storage->fetchAll('user_roles');
        $cnt = count($entities);
        
        // If the requested page is later than the last, redirect to the last
        /*if ($cnt && $pagdata['page'] > $cnt) {
            return $Response
                ->withStatus(302)
                ->withHeader('Location', sprintf('%s?page-categories=%d', $path, $cnt));
        }*/
        
        $entities->setItemCountPerPage($pagdata['size']);
        $entities->setCurrentPageNumber($pagdata['page']);

        // $data['pages'] = iterator_to_array($pages->getItemsByPage($page));
        $data['user_roles'] = iterator_to_array($entities->getCurrentItems());
        
        // pagination data
        $data['pagination'] = [];
        $data['pagination']['page'] = $pagdata['page'];
        $data['pagination']['size'] = $pagdata['size'];
        $data['pagination']['count'] = $cnt;
        
        // return new JsonResponse($data);
        return new HtmlResponse($this->template->render('role::list', $data));
        // return new HtmlResponse($this->template->render('page::category-list', $data));
        
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        if ('POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // var_dump($post);exit(__FILE__.'::'.__LINE__);
            $id = $this->storage->insert('user_roles',$post);
            if(!empty($id)){
                $url = $this->router->generateUri('admin.auth.role', ['action' => 'edit','id' => $id]);
                return $Response
                    ->withStatus(302)
                    ->withHeader('Location', (string) $url);
            }
        }
        
        $data['role_parents'] = $this->storage->parents('user_roles');
        
        return new HtmlResponse($this->template->render('role::add', $data));
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        
        if (!empty($id) && 'POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // var_dump($post);exit(__FILE__.'::'.__LINE__);
            $this->storage->update('user_roles',$post, ['id' => $id]);
            $url = $this->router->generateUri('admin.auth.role', ['action' => 'edit','id' => $id]);
            return $Response
                ->withStatus(302)
                ->withHeader('Location', (string) $url);
        }
        
        $data['user_role'] = $this->storage->fetch('user_roles',$id);
        // $data['role_parents'] = $this->storage->fetchAll('user_roles',[]);
        $data['role_parents'] = $this->storage->parents('user_roles',$id);
        
        return new HtmlResponse($this->template->render('role::edit', $data));
    }
    
}

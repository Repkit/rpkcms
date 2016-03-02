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
        
        $entities = $this->storage->fetchAll('pages');
        $cnt = count($entities);
        
        // If the requested page is later than the last, redirect to the last
        /*if ($cnt && $pagdata['page'] > $cnt) {
            return $Response
                ->withStatus(302)
                ->withHeader('Location', sprintf('%s?page=%d', $path, $cnt));
        }*/
        
        $entities->setItemCountPerPage($pagdata['size']);
        $entities->setCurrentPageNumber($pagdata['page']);

        // $data['pages'] = iterator_to_array($pages->getItemsByPage($page));
        $data['pages'] = iterator_to_array($entities->getCurrentItems());
        
        // return new JsonResponse($data);
        return new HtmlResponse($this->template->render('templates/page::list', $data));
        
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        if ('POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // var_dump($post);exit();
            $id = $this->storage->insert('pages',$post);
            if(!empty($id)){
                $url = $this->router->generateUri('admin.page', ['action' => 'edit','id' => $id]);
                return $Response
                    ->withStatus(302)
                    ->withHeader('Location', (string) $url);
            }
        }
        
        $data['page_templates'] = $this->storage->fetchAll('page_templates');
        $data['page_parents'] = $this->storage->fetchAll('pages',[]);
        $data['page_categories'] = $this->storage->fetchAll('page_categories',[]);
        $data['user_roles'] = $this->storage->fetchAll('user_roles',[]);
        $data['page_statuses'] = $this->storage->fetchAll('page_statuses');
        
        return new HtmlResponse($this->template->render('templates/page::add', $data));
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        if (!empty($id) && 'POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            unset($post['files']);
            // var_dump($post);exit();
            $this->storage->update('pages',$post, ['id' => $id]);
            $url = $this->router->generateUri('admin.page', ['action' => 'edit','id' => $id]);
            
            // delete cached file
            $cachepath = getcwd().'/public/data/cache/html/';
            $file = $cachepath.$post['slug'].'.html';
            if(file_exists($file)){
                 unlink(realpath($file));
            }
            
            return $Response
                ->withStatus(302)
                ->withHeader('Location', (string) $url);
        }
        
        // gather all data required for composing page
        $data['page'] = $this->storage->fetch('pages',$id);
        $data['page_templates'] = $this->storage->fetchAll('page_templates');
        $data['page_parents'] = $this->storage->fetchAll('pages',[]);
        $data['page_categories'] = $this->storage->fetchAll('page_categories',[]);
        $data['user_roles'] = $this->storage->fetchAll('user_roles',[]);
        $data['page_statuses'] = $this->storage->fetchAll('page_statuses');
        $data['page_meta'] = $this->storage->fetchAll('page_meta',['pageId'=>$id]);
        $data['page_meta_tags'] = $this->storage->fetchAll('page_meta_tags',['pageId'=>$id]);
        
        return new HtmlResponse($this->template->render('templates/page::edit', $data));
    }
    
}

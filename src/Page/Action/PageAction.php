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
        /* - we dont need to generate html links here in order to avoid recursion 
        *  - for getting page path based on category hierarchy
        * $entities = $this->storage->fetchAllPages();
        */
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
        
        // pagination data
        $data['pagination'] = [];
        $data['pagination']['page'] = $pagdata['page'];
        $data['pagination']['size'] = $pagdata['size'];
        $data['pagination']['count'] = $cnt;
        
        // return new JsonResponse($data);
        return new HtmlResponse($this->template->render('page::list', $data));
        
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        if ('POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            unset($post['files']);
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
        // $data['page_parents'] = $this->storage->parents('pages');
        $data['page_categories'] = $this->storage->fetchAll('page_categories',[]);
        $data['user_roles'] = $this->storage->fetchAll('user_roles',[]);
        $data['page_statuses'] = $this->storage->fetchAll('page_statuses');
        
        return new HtmlResponse($this->template->render('page::add', $data));
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        if (!empty($id) && 'POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            unset($post['files']);
            $this->storage->update('pages',$post, ['id' => $id]);
            $url = $this->router->generateUri('admin.page', ['action' => 'edit','id' => $id]);
            
            // delete cached file
            // TODO [IMPROVEMENT]: get from config or pass responsability to a service
            $cachepath = getcwd().'/public/data/cache/html';
            $data['page'] = $this->storage->pageById($id);
            // $file = $cachepath.$post['slug'].'.html';
            $file = $cachepath. $data['page']['path'].'/'.$data['page']['slug'].'.html';
            if(file_exists($file)){
                 unlink(realpath($file));
            }
            
            return $Response
                ->withStatus(302)
                ->withHeader('Location', (string) $url);
        }else{
            $data['page'] = $this->storage->fetch('pages',$id);
        }
        
        // gather all data required for composing page
        $data['page_templates'] = $this->storage->fetchAll('page_templates');
        // $data['page_parents'] = $this->storage->fetchAll('pages',[]);
        $data['page_parents'] = $this->storage->parents('pages',$id);
        $data['page_categories'] = $this->storage->fetchAll('page_categories',[]);
        $data['user_roles'] = $this->storage->fetchAll('user_roles',[]);
        $data['page_statuses'] = $this->storage->fetchAll('page_statuses');
        $data['page_meta'] = $this->storage->fetchAll('page_meta',['pageId'=>$id]);
        $data['page_meta_tags'] = $this->storage->fetchAll('page_meta_tags',['pageId'=>$id]);
        
        return new HtmlResponse($this->template->render('page::edit', $data));
    }
    
    public function previewAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        // if not post just continue
        if ('POST' !== $Request->getMethod()) {
            return $Next($Request,$Response);
        }
        
        $data = [];
        $post = $Request->getParsedBody();
        // var_dump($post);exit(__FILE__.'::'.__LINE__);
        
        $pagedb = $this->storage->pageBySlug($post['slug']);
        // if the template changed then get path of the new template
        if($pagedb['templateId'] !== $post['templateId']){
            $templatedb = $this->storage->fetch('page_templates',$post['templateId']);
            $post['page_templates.path'] = $templatedb['path'];
            $post['page_templates.name'] = $templatedb['name'];
        }
        $pagedb = \Zend\Stdlib\ArrayUtils::merge($pagedb,$post);
        
        // copy-paste from CacheAction
        //TODO [IMPROVEMENT]: avoid code duplication from CacheAction
        $content = $this->template->render('templates'.$pagedb['page_templates.path'].'::'.$pagedb['page_templates.name'], $pagedb);
        
        return new HtmlResponse($content);
    }
    
}

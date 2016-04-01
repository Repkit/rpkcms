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

class CategoryAction
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
        
        $entities = $this->storage->fetchAll('page_categories');
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
        $data['page_categories'] = iterator_to_array($entities->getCurrentItems());
        
        // return new JsonResponse($data);
        return new HtmlResponse($this->template->render('page/category::list', $data));
        // return new HtmlResponse($this->template->render('page::category-list', $data));
        
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        if ('POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            $id = $this->storage->insert('page_categories',$post);
            if(!empty($id)){
                $url = $this->router->generateUri('admin.page-category', ['action' => 'edit','id' => $id]);
                return $Response
                    ->withStatus(302)
                    ->withHeader('Location', (string) $url);
            }
        }
        
        $data['page_category_parents'] = $this->storage->parents('page_categories');
        return new HtmlResponse($this->template->render('page/category::add', $data));
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        
        if (!empty($id) && 'POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // init parent
            $initParentId = $post['initParentId'];
            unset($post['initParentId']);
            //init slug
            $initSlug = $post['initSlug'];
            unset($post['initSlug']);
            //init state
            $initState = $post['initState'];
            unset($post['initState']);
            $this->storage->update('page_categories',$post, ['id' => $id]);
            $url = $this->router->generateUri('admin.page-category', ['action' => 'edit','id' => $id]);
            //if parent has changed then reorganize folder structure
            if($initParentId !== $post['parentId']){
                //TODO : delete cache of pages or move directories
            }
            //if slug has changed then rename the old folder
            if($initSlug !== $post['slug']){
                //TODO : rename category folder according to new slug
            }
            //if state has changed then update state for all subcategories and also for all respective pages
            if($initState !== $post['state']){
                //TODO : maybe with trigger
            }
            return $Response
                ->withStatus(302)
                ->withHeader('Location', (string) $url);
        }
        
        $entity = $this->storage->fetch('page_categories',$id);
        $data['page_category'] = $entity;
        $data['page_category_parents'] = $this->storage->parents('page_categories',$id);
        return new HtmlResponse($this->template->render('page/category::edit', $data));
    }
    
}

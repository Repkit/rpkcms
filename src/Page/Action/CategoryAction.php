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
        
        // pagination data
        $data['pagination'] = [];
        $data['pagination']['page'] = $pagdata['page'];
        $data['pagination']['size'] = $pagdata['size'];
        $data['pagination']['count'] = $cnt;
        
        // return new JsonResponse($data);
        return new HtmlResponse($this->template->render('page/category::list', $data));
        // return new HtmlResponse($this->template->render('page::category-list', $data));
        
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        if ('POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // TODO [IMPROVEMENT]: implement hydrator so only db fields are used
            $post['creationDate'] = date('Y-m-d H:i:s');
            $id = $this->storage->insert('page_categories',$post);
            if(!empty($id)){
                $url = $this->router->generateUri('admin.page-category', ['action' => 'edit','id' => $id]);
                return $Response
                    ->withStatus(302)
                    ->withHeader('Location', (string) $url);
            }
        }
        
        $data['page_category_parents'] = $this->storage->fetchAll('page_categories',[]);
        return new HtmlResponse($this->template->render('page/category::add', $data));
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        
        if (!empty($id) && 'POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // var_dump($post);exit(__FILE__.'::'.__LINE__);
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
            
            //if state has changed then update state for all subcategories and also for all respective pages
            // this has higher priority that parent or slug
            if($initState !== $post['state']){
                //TODO [IMPROVEMENT]: maybe with trigger
            }elseif($initParentId !== $post['parentId']){
                //if parent has changed then reorganize folder structure
                $this->changeParent($initParentId, $post['parentId'], $initSlug, $post['slug']);
            }elseif($initSlug !== $post['slug']){
                //if slug has changed then rename the old folder
                //here we are assuming that we use mv unix commad to rename also
                $this->changeParent($initParentId, $post['parentId'], $initSlug, $post['slug']);
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
    
    //TODO [IMPROVEMENT]: move to cache service
    private function changeParent($OldParentId, $NewParentId, $OldNme, $NewName)
    {
        try{
            $oldid = intval($OldParentId);
            $newid = intval($NewParentId);
            if(empty($oldid) || empty($newid)){
                throw \Exception ('empty data!');
            }
            // $cachepath = getcwd().'/public/data/cache/html';
            $cachepath = \Page\ModuleConfig::cachepath();
            
            // determine old path
            $oldpath = $this->storage->getPageCateoryPathById($oldid);
            // var_dump($oldpath);exit(__FILE__.'::'.__LINE__);
            if($oldpath){
                // determine new path
                if($oldid === $newid){
                    $newpath = $oldpath;
                }else{
                    $newpath = $this->storage->getPageCateoryPathById($newid);
                }
                $oldpath = $cachepath.$oldpath['path'].DIRECTORY_SEPARATOR.$OldNme;
                $newpath = $cachepath.$newpath['path'].DIRECTORY_SEPARATOR.$NewName;
            }else{
                throw \Exception ('could not determine old path!');
            }
            
            if($newpath){
                // var_dump("mv $oldpath $newpath");exit(__FILE__.'::'.__LINE__);
                exec("mv $oldpath $newpath");
                // $ok = file_exists($newpath);
                $ok = true; //fake use file_exists($newpath); for real status
            }else{
                $ok = false;
            }
            
            if(!$ok){
                // if move directory failed then delete old one as 
                // the new one will be generated when first file will be requested
                exec("rm -rf $oldpath");
                // $ok = !file_exists($oldpath);
                $ok = true; //fake use !file_exists($oldpath); for real status
            }
            
            return $ok;
            
        }catch(\Exception $e){
            return false;
        }
        
    }
    
}

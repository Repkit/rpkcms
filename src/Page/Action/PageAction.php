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
        $plugin = \RpkPluginManager\PluginChain::getInstance();
        
        if ('POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // var_dump($post);exit(__FILE__.'::'.__LINE__);
            unset($post['files']);
            
            // meta data
            // TODO [IMPROVEMENT]: maybe use a separate middleware that will handle page meta as there are different tables
            //                      use hydrator and put in request parsedBody the inserted id and return next not response
            //                      so the page_meta middleware get in action and save data using his hydrator
            if(isset($post['meta'])){
                $meta = $post['meta'];
                unset($post['meta']);
            }
            
            // meta_tags data
            if(isset($post['meta_tags'])){
                $meta_tags = $post['meta_tags'];
                unset($post['meta_tags']);
            }
            
            // TODO [IMPROVEMENT]: implement hydrator so only db fields are used
            $params = $plugin->prepareArgs(['data' => $post]);
            // keep a blue print of the original params - useful for plugin
            $orgparams = clone $params;
            // trigger event where plugin must UNSET their data
            $plugin->trigger('page::add-insert.pre', $params);
            // get the clean post after plugins unset their data
            $post = $params['data'];
            
            // TODO [IMPROVEMENT]: implement hydrator so only db fields are used
            $post['creationDate'] = date('Y-m-d H:i:s');
            $id = $this->storage->insert('pages',$post);
            if(!empty($id)){
                
                $authorId = !empty($post['authorId'])?$post['authorId']:1;
                
                // persist meta
                if(!empty($meta) && is_array($meta) && !empty($meta['key']) 
                    && !empty($meta['value']) && count($meta['key']) == count($meta['value'])){
                        
                    foreach($meta['key'] as $idx => $key){
                        // validate and filter key=>value
                        if(!isset($key) || empty($key) || 
                            !isset($meta['value'][$idx]) || empty($meta['value'][$idx])){
                            continue;
                        }
                        $metadata = [
                            'pageId' => $id, 
                            'metaKey' => $key, 
                            'metaValue' => $meta['value'][$idx],
                            // 'creationDate' => date('Y-m-d H:i:s'),
                            'creationDate' => $post['creationDate'],
                            'authorId' => $authorId
                        ];
                        
                        $this->storage->insert('page_meta',$metadata);
                    }
                }
                
                // persist meta_tags
                if(!empty($meta_tags) && is_array($meta_tags) && !empty($meta_tags['value'])){
                        
                    foreach($meta_tags['value'] as $idx => $value){
                        // validate and filter value
                        if(!isset($value) || empty($value)){
                            continue;
                        }
                        $metatagsdata = [
                            'pageId' => $id, 
                            'value' => $value, 
                            // 'creationDate' => date('Y-m-d H:i:s'),
                            'creationDate' => $post['creationDate'],
                            'authorId' => $authorId
                        ];
                        
                        $this->storage->insert('page_meta_tags',$metatagsdata);
                    }
                }
                
                $url = $this->router->generateUri('admin.page', ['action' => 'edit','id' => $id]);
                
                $plugin->trigger('page::add-insert.post', ['id' => $id, 'data' => $orgparams['data']]);
                
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
        
        $plugin = \RpkPluginManager\PluginChain::getInstance();
        $params = $plugin->prepareArgs(['template'=> 'page::add', 'data' => $data]);
        $plugin->trigger('page::add-render.pre', $params);
        
        // return new HtmlResponse($this->template->render('page::add', $data));
        $htmlResponse = new HtmlResponse($this->template->render($params['template'], $params['data']));
        
        $response = $Next($Request, $htmlResponse); 
        
        return $response;
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        if (!empty($id) && 'POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // var_dump($post);exit(__FILE__.'::'.__LINE__);
            unset($post['files']);
            
            // meta data
            // TODO [IMPROVEMENT]: maybe use a separate middleware that will handle page meta as there are different tables
            //                      use hydrator and put in request parsedBody the inserted id and return next not response
            //                      so the page_meta middleware get in action and save data using his hydrator
            if(isset($post['meta'])){
                $meta = $post['meta'];
                unset($post['meta']);
            }
            
            // meta_tags data
            if(isset($post['meta_tags'])){
                $meta_tags = $post['meta_tags'];
                unset($post['meta_tags']);
            }
            
            $plugin = \RpkPluginManager\PluginChain::getInstance();
            $params = $plugin->prepareArgs(['id'=> $id, 'data' => $post]);
            $plugin->trigger('page::edit-update.pre', $params);
            $post = $params['data'];
            
            $this->storage->update('pages',$post, ['id' => $id]);
            
            // update page meta
            $this->storage->delete('page_meta', ['pageId' => $id]);
            
            // update page meta tags
            // note: we don't implement onchange flag strategy in template because is tricky for copy-pasting in textarea
            $this->storage->delete('page_meta_tags', ['pageId' => $id]);
            
            $authorId = !empty($post['authorId'])?$post['authorId']:1;
            
            // persist meta
            if(!empty($meta) && is_array($meta) && !empty($meta['key']) 
                && !empty($meta['value']) && count($meta['key']) == count($meta['value'])){
                    
                foreach($meta['key'] as $idx => $key){
                    // validate and filter key=>value
                    if(!isset($key) || empty($key) || 
                        !isset($meta['value'][$idx]) || empty($meta['value'][$idx])){
                        continue;
                    }
                    $metadata = [
                        'pageId' => $id, 
                        'metaKey' => $key, 
                        'metaValue' => $meta['value'][$idx],
                        'creationDate' => date('Y-m-d H:i:s'),
                        'authorId' => $authorId
                    ];
                    
                    $this->storage->insert('page_meta',$metadata);
                }
            }
            
            // persist meta_tags
            if(!empty($meta_tags) && is_array($meta_tags) && !empty($meta_tags['value'])){
                    
                foreach($meta_tags['value'] as $idx => $value){
                    // validate and filter value
                    if(!isset($value) || empty($value)){
                        continue;
                    }
                    $metatagsdata = [
                        'pageId' => $id, 
                        'value' => $value, 
                        'creationDate' => date('Y-m-d H:i:s'),
                        'authorId' => $authorId
                    ];
                    
                    $this->storage->insert('page_meta_tags',$metatagsdata);
                }
            }
            
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
        
        $plugin = \RpkPluginManager\PluginChain::getInstance();
        $params = $plugin->prepareArgs(['template'=> 'page::edit', 'data' => $data]);
        $plugin->trigger('page::edit-render.pre', $params);
        
        // return new HtmlResponse($this->template->render('page::edit', $data));
        $htmlResponse = new HtmlResponse($this->template->render($params['template'], $params['data']));
        
        $response = $Next($Request, $htmlResponse); 
        
        return $response;
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
        // TODO [IMPROVEMENT]: avoid code duplication from CacheAction
        $content = $this->template->render('templates'.$pagedb['page_templates.path'].'::'.$pagedb['page_templates.name'], $pagedb);
        
        return new HtmlResponse($content);
    }
    
}

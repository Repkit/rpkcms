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

class TemplateAction
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
        // $path = $Request->getOriginalRequest()->getUri()->getPath();
        $pagdata = $this->getPaginationDataFromRequest($Request);
        
        $entities = $this->storage->fetchAll('page_templates');
        $cnt = count($entities);
        
        // If the requested page is later than the last, redirect to the last
        /*if ($cnt && $pagdata['page'] > $cnt) {
            return $Response
                ->withStatus(302)
                ->withHeader('Location', sprintf('%s?page-categories=%d', $path, $cnt));
        }*/
        
        $entities->setItemCountPerPage($pagdata['size']);
        $entities->setCurrentPageNumber($pagdata['page']);

        $data['page_templates'] = iterator_to_array($entities->getCurrentItems());
        
        // pagination data
        $data['pagination'] = [];
        $data['pagination']['page'] = $pagdata['page'];
        $data['pagination']['size'] = $pagdata['size'];
        $data['pagination']['count'] = $cnt;
        
        // return new JsonResponse($data);
        return new HtmlResponse($this->template->render('page/template::list', $data));
        // return new HtmlResponse($this->template->render('page::category-list', $data));
        
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $plugin = \RpkPluginManager\PluginChain::getInstance();
        
        if ('POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // TODO [SECURITY]: validate content
            $content = $post['content'];
            unset($post['content']);
            $post['creationDate'] = date('Y-m-d H:i:s');
            
            $params = $plugin->prepareArgs(['data' => $post]);
            // keep a blue print of the original params - useful for plugin
            $orgparams = clone $params;
            // trigger event where plugin must UNSET their data
            $plugin->trigger('page/template::add-insert.pre', $params);
            // get the clean post after plugins unset their data
            $post = $params['data'];
            
            // TODO [IMPROVEMENT]: implement hydrator so only db fields are used
            $id = $this->storage->insert('page_templates',$post);
            if(!empty($id)){
                $themepath = \Page\ModuleConfig::themepath();
                // $path = getcwd().'/templates'.$post['path'];
                $path = $themepath.$post['path'];
                if(substr($path,-1) !== '/'){
                	$path .= '/';
                }
                if (!is_dir($path)) {
                  // dir doesn't exist, make it
                  mkdir($path, 0775, true);
                }
                file_put_contents($path.$post['name'].'.html.twig', $content);
                $url = $this->router->generateUri('admin.page-template', ['action' => 'edit','id' => $id]);
                
                $plugin->trigger('page/template::add-insert.post', ['id' => $id, 'data' => $orgparams['data']]);
                
                return $Response
                    ->withStatus(302)
                    ->withHeader('Location', (string) $url);
            }
            
            // $data = $post;
            $data = $orgparams['data'];
            $data['content'] = $content;
        }
        
        // return new HtmlResponse($this->template->render('page/template::add', $data));
        $params = $plugin->prepareArgs(['template'=> 'page/template::add', 'data' => $data]);
        $plugin->trigger('page/template::add-render.pre', $params);
        
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
            // TODO [SECURITY]: validate content
            $content = $post['content'];
            unset($post['content']);
            
            $themepath = \Page\ModuleConfig::themepath();
            // $path = getcwd().'/templates'.$post['path'];
            $path = $themepath.$post['path'];
            if(substr($path,-1) !== '/'){
            	$path .= '/';
            }
            if (!is_dir($path)) {
              // dir doesn't exist, make it
              mkdir($path, 0775, true);
            }
            $file = $path.$post['name'].'.html.twig';
            if(file_exists($file)){
                //backup file per day
                $bakfile = $file.'.'.date('Ymd').'.bak';
                if (!copy($file, $bakfile)) {
                    throw new \Exception('failed to backup template: '. $file);
                }
            }
            file_put_contents($file, $content);
            try{
                
                $plugin = \RpkPluginManager\PluginChain::getInstance();
                $params = $plugin->prepareArgs(['id'=> $id, 'data' => $post]);
                $plugin->trigger('page/template::edit-update.pre', $params);
                $post = $params['data'];
                
                $this->storage->update('page_templates', $post, ['id' => $id]);
            }catch(\Exception $e){
                // var_dump($e->getMessage());exit(__FILE__.'::'.__LINE__);
                if (!copy($bakfile, $file)) {
                    throw new \Exception('failed to update template and to restore content of the old one from: '. $bakfile);
                }else{
                    throw new \Exception('failed to update template the old content was restored!');
                }
            }
            
            // delete cache of the pages that use this template
            // TODO [PERFORMANCE]: query only for slug column
            $pages = $this->storage->fetchAllPages(['templateId'=>$id]);
            // $cachepath = getcwd().'/public/data/cache/html/';
            $cachepath = \Page\ModuleConfig::cachepath();
            foreach($pages as $page){
                // $file = $cachepath.$page['slug'].'.html';
                $file = $cachepath. $page['path'].'/'.$page['slug'].'.html';
                if(file_exists($file)){
                     unlink(realpath($file));
                }
            }
            $url = $this->router->generateUri('admin.page-template', ['action' => 'edit','id' => $id]);
            return $Response
                ->withStatus(302)
                ->withHeader('Location', (string) $url);
        }
        
        $entity = $this->storage->fetch('page_templates',$id);
        $themepath = \Page\ModuleConfig::themepath();
        // $content = file_get_contents(getcwd().'/templates'.$entity['path'].$entity['name'].'.html.twig');
        $content = file_get_contents($themepath.$entity['path'].$entity['name'].'.html.twig');
        $data['page_template'] = $entity;
        $data['page_template']['content'] = $content;
        
        // return new HtmlResponse($this->template->render('page/template::edit', $data));
        
        $plugin = \RpkPluginManager\PluginChain::getInstance();
        $params = $plugin->prepareArgs(['template'=> 'page/template::edit', 'data' => $data]);
        $plugin->trigger('page/template::edit-render.pre', $params);
        
        $htmlResponse = new HtmlResponse($this->template->render($params['template'], $params['data']));
        
        $response = $Next($Request, $htmlResponse); 
        
        return $response;
    }
    
}

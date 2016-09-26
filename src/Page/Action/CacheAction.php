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

class CacheAction
{
    private $router;

    private $template;
    
    private $storage;

    public function __construct(StorageInterface $Storage, Router\RouterInterface $Router
        , Template\TemplateRendererInterface $Template = null)
    {
        $this->storage  = $Storage;
        $this->router   = $Router;
        $this->template = $Template;
    }

    public function __invoke(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        try{
        
            $url  = $Request->getUri();
            $path = $url->getPath();
            $query = $url->getQuery();
            // var_dump($url);exit(__FILE__.'::'.__LINE__);
            
            //detecting language
            // $lang = $Request->getAttribute('lang', 'en');
            
            //detecting requested page
            // TODO [PERFORMANCE]: check if better get from request first
            if(preg_match('#\.(html|xml)$#', $path, $matches)){
                $page = substr($path,1,-strlen(reset($matches)));
            }else{
                // $page = $Request->getAttribute('page', 'index');
                if('/' == $path){
                    $page = 'index';
                    $path = '/index.html';
                }else{
                    $page = substr($path,1);
                    $path .= '.html';
                }
            }
            $filename = $page;
            $pageschema = explode('/',$page);
            $page = array_pop($pageschema);
            unset($pageschema);
            // var_dump($page);exit(__FILE__.'::'.__LINE__);
    
            $data['page'] = $page;
            // $data['language'] = date('Y-m-d H:i:s');
            
            // $file = getcwd().'/data/cache/html/'.$lang.'_'.$page.'.html';
            // TODO [IMPROVEMENT]: get from config or pass responsability to a service
            $cachepath = getcwd().'/public/data/cache/html';
            $file = $cachepath.'/'.$filename.'.html';
            // var_dump($file);exit(__FILE__.'::'.__LINE__);
            if(!file_exists($file)){
                // $pagedb = $this->storage->fetch('pages', $page, 'slug');
                $pagedb = $this->storage->pageBySlug($page);
                // var_dump($pagedb);exit(__FILE__.'::'.__LINE__);
                if(false == $pagedb){
                    throw new StorageException('Page not found');
                }
                
                // load page extra info
                $data['page_meta'] = $this->storage->fetchAll('page_meta',['pageId'=>$pagedb['id']]);
                $data['page_meta_tags'] = $this->storage->fetchAll('page_meta_tags',['pageId'=>$pagedb['id']]);
                
                $pagedb = \Zend\Stdlib\ArrayUtils::merge($pagedb,$data);
                // var_dump($pagedb['content']);exit(__FILE__.'::'.__LINE__);
                // $content = file_get_contents(getcwd().'/data/cache/html/en_test.html');
                
                $plugin = \RpkPluginManager\PluginChain::getInstance();
                $params = $plugin->prepareArgs(['template'=> 'templates'.$pagedb['page_templates.path'].'::'.$pagedb['page_templates.name'], 'data' => $pagedb]);
                $plugin->trigger('page::cache-render.pre', $params);
                
                // $content = $this->template->render('templates'.$pagedb['page_templates.path'].'::'.$pagedb['page_templates.name'], $pagedb);
                $content = $this->template->render($params['template'], $params['data']);
                // var_dump($content);exit(__FILE__.'::'.__LINE__);
                
                $pagename = '/'.$pagedb['slug'].'.html';
                $pagedir = $cachepath . $pagedb['path'];
                if (!is_dir($pagedir)) {
                  // dir doesn't exist, make it
                  mkdir($pagedir, 0777, true);
                }
                $file = $pagedir.$pagename;
                if(file_put_contents($file, $content)){
                    $path = $pagedb['path'].$pagename;
                }
                
            }
            
            // var_dump($path);exit(__FILE__.'::'.__LINE__);
            
            // return $this->redirect($path.'.html', $url, $Response);
            // return $this->redirect("/data/cache/html/$filename", $url, $Response);
            return $this->redirect($path, $url, $Response);
            
        }catch(\Exception $e){
            // var_dump($e->getMessage());exit(__FILE__.'::'.__LINE__);
            return $Next($Request,$Response);
        }
    }
    
    private function redirect($Path, $Url, $Res, $Status = 202, $Query = [])
    {
        
        $url = $Url->withPath($Path);

        if (count($Query)) {
            $url = $url->withQuery(http_build_query($Query));
        }
        

        return $Res
            // ->withStatus(301)
            ->withStatus($Status)
            ->withHeader('Location', (string) $url);
    }
    
}
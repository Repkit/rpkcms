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
            $query = $url->getQuery;
            // var_dump($url);exit();
            
            //detecting language
            // $lang = $Request->getAttribute('lang', 'en');
            
            //detecting requested page
            // TODO: [PERFORMANCE] -check if better get from request first
            if(preg_match('#\.(html|xml)$#', $path, $matches)){
                $page = substr($path,1,-strlen(reset($matches)));
            }else{
                $page = $Request->getAttribute('page', 'index');
                if('/' == $path){
                    $path = '/index.html';
                }else{
                    $path .= '.html';
                }
            }
            // var_dump($page);exit();
    
            $data['page'] = $page;
            $data['language'] = date('Y-m-d H:i:s');
            
            
            // $file = getcwd().'/data/cache/html/'.$lang.'_'.$page.'.html';
            // TODO [IMPROVEMENT]: get from config or pass responsability to a service
            $cachepath = getcwd().'/public/data/cache/html/';
            $filename = $page.'.html';
            $file = $cachepath.$filename;
            // var_dump($file);exit();
            if(!file_exists($file)){
                // $pagedb = $this->storage->fetch('pages', $page, 'slug');
                $pagedb = $this->storage->pageBySlug($page);
                // var_dump($pagedb);exit();
                if(false == $pagedb){
                    throw new StorageException('Page not found');
                }
                $pagedb = \Zend\Stdlib\ArrayUtils::merge($pagedb,$data);
                // var_dump($pagedb['content']);exit();
                // $content = file_get_contents(getcwd().'/data/cache/html/en_test.html');
                $content = $this->template->render('templates'.$pagedb['page_templates.path'].'::'.$pagedb['page_templates.name'], $pagedb);
                file_put_contents($file, $content);
            }
            
            // var_dump($path);exit();
            
            // return $this->redirect($path.'.html', $url, $Response);
            // return $this->redirect("/data/cache/html/$filename", $url, $Response);
            return $this->redirect($path, $url, $Response);
            
        }catch(\Exception $e){
            // var_dump($e->getMessage());exit();
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

<?php

namespace Page\Action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Expressive\Plates\PlatesRenderer;
use Zend\Expressive\Twig\TwigRenderer;
use Zend\Expressive\ZendView\ZendViewRenderer;

class CacheAction
{
    private $router;

    private $template;
    
    private $pdo;

    public function __construct(\PDO $Pdo, Router\RouterInterface $Router
        , Template\TemplateRendererInterface $Template = null)
    {
        $this->pdo      = $Pdo;
        $this->router   = $Router;
        $this->template = $Template;
    }

    public function __invoke(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $url  = $Request->getUri();
        $path = $url->getPath();
        $query = $url->getQuery;
        // var_dump($url);exit();
        
        //detecting language
        // $lang = $Request->getAttribute('lang', 'en');
        
        //detecting requested page
        //TODO: TIME-check if better get from request first
        if(preg_match('#\.(html|xml)$#', $path, $matches)){
            $page = substr($path,1,-strlen(reset($matches)));
        }else{
            $page = $Request->getAttribute('page', 'home');
        }
       
        $data['page'] = $page;
        $data['language'] = date('Y-m-d H:i:s');
        
        
        // $file = getcwd().'/data/cache/html/'.$lang.'_'.$page.'.html';
        $cachepath = getcwd().'/public/data/cache/html/';
        $filename = $page.'.html';
        $file = $cachepath.$filename;
        if(!file_exists($file)){
            $content = $this->template->render('page::home-page', $data);
            // $data = file_get_contents(getcwd().'/data/cache/html/en_test.html');
            file_put_contents($file, $content);
        }
        
        // return $this->redirect($path.'.html', $url, $Response);
        // return $this->redirect("/data/cache/html/$filename", $url, $Response);
        return $this->redirect($path, $url, $Response);
    }
    
    private function redirect($path, $url, $res, $query = [])
    {
        
        $url = $url->withPath($path);

        if (count($query)) {
            $url = $url->withQuery(http_build_query($query));
        }
        

        return $res
            // ->withStatus(301)
            ->withStatus(202)
            ->withHeader('Location', (string) $url);
    }
}

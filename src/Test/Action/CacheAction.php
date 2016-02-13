<?php

namespace Test\Action;

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

    public function __construct(Router\RouterInterface $router, Template\TemplateRendererInterface $template = null)
    {
        $this->router   = $router;
        $this->template = $template;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $data = [];

        $lang = $request->getAttribute('lang', 'en');
        $data['language'] = $lang;
        $page = $request->getAttribute('page', 'home');
        $data['page'] = $page;
        
        $file = getcwd().'/data/cache/html/'.$lang.'_'.$page.'.html';
        if(!file_exists($file)){
            $data = $this->template->render('test::home-page', $data);
            // $data = file_get_contents(getcwd().'/data/cache/html/en_test.html');
            file_put_contents($file, $data);
        }else{
            echo 'from Cache';
            $data = file_get_contents($file);
        }

        return new HtmlResponse($data);
    }
}

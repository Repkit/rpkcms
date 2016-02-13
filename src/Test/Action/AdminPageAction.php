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

class AdminPageAction
{
    private $router;

    private $template;
    
    use DispacherTrait;

    public function __construct(Router\RouterInterface $router, Template\TemplateRendererInterface $template = null)
    {
        $this->router   = $router;
        $this->template = $template;
    }

    public function indexAction(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $data = [];
        return new HtmlResponse($this->template->render('test::list', $data));
    }
    
    public function addAction(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $data = [];
        return new HtmlResponse($this->template->render('test::add', $data));
    }
    
    public function editAction(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $data = [];
        $id = $request->getAttribute('id', false);
        $data['id'] = $id;
        return new HtmlResponse($this->template->render('test::edit', $data));
    }
    
}

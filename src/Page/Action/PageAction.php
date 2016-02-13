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

class PageAction
{
    private $router;

    private $template;
    
    private $pdo;
    
    use DispacherTrait;

    public function __construct(\PDO $Pdo, Router\RouterInterface $Router
        , Template\TemplateRendererInterface $Template = null)
    {
        $this->pdo      = $Pdo;
        $this->router   = $Router;
        $this->template = $Template;
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        return new HtmlResponse($this->template->render('page::add', $data));
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        $data['id'] = $id;
        return new HtmlResponse($this->template->render('page::edit', $data));
    }
}

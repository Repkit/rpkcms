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
        $path = $Request->getOriginalRequest()->getUri()->getPath();
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
        
        // return new JsonResponse($data);
        return new HtmlResponse($this->template->render('templates/page/template::list', $data));
        // return new HtmlResponse($this->template->render('page::category-list', $data));
        
    }

    public function addAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        if ('POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // var_dump($post);exit();
            // TODO: [SECURITY] - validate content
            $content = $post['content'];
            unset($post['content']);
            $post['creationDate'] = date('Y-m-d H:i:s');
            
            $id = $this->storage->insert('page_templates',$post);
            if(!empty($id)){
                $path = getcwd().'/templates'.$post['path'];
                if(substr($path,-1) !== '/'){
                	$path .= '/';
                }
                if (!is_dir($path)) {
                  // dir doesn't exist, make it
                  mkdir($path, 0775, true);
                }
                file_put_contents($path.$post['name'].'.html.twig', $content);
                $url = $this->router->generateUri('admin.page-template', ['action' => 'edit','id' => $id]);
                return $Response
                    ->withStatus(302)
                    ->withHeader('Location', (string) $url);
            }
            
            $data = $post;
            $data['content'] = $content;
        }
        
        return new HtmlResponse($this->template->render('templates/page/template::add', $data));
    }
    
    public function editAction(ServerRequestInterface $Request, ResponseInterface $Response, callable $Next = null)
    {
        $data = [];
        $id = $Request->getAttribute('id', false);
        
        if (!empty($id) && 'POST' === $Request->getMethod()) {
            $post = $Request->getParsedBody();
            // var_dump($post);exit();
            // TODO: [SECURITY] - validate content
            $content = $post['content'];
            unset($post['content']);
            
            
            $path = getcwd().'/templates'.$post['path'];
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
                $this->storage->update('page_templates',$post, ['id' => $id]);
            }catch(\Exception $e){
                if (!copy($bakfile, $file)) {
                    throw new \Exception('failed to update template and to restore content of the old one from: '. $bakfile);
                }else{
                    throw new \Exception('failed to update template the old content was restored!');
                }
            }
            
            
            // TODO: delete cache for pages that use this template
            $url = $this->router->generateUri('admin.page-template', ['action' => 'edit','id' => $id]);
            return $Response
                ->withStatus(302)
                ->withHeader('Location', (string) $url);
        }
        
        $entity = $this->storage->fetch('page_templates',$id);
        // var_dump($entity);exit();
        $content = file_get_contents(getcwd().'/templates'.$entity['path'].$entity['name'].'.html.twig');
        $data['page_template'] = $entity;
        $data['page_template']['content'] = $content;
        return new HtmlResponse($this->template->render('templates/page/template::edit', $data));
    }
    
}

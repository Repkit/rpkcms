<?php

namespace Plugin\Paginator;

use Page\Storage\StorageInterface;
use Zend\Expressive\Template;

class Paginator
{
    private $storage;
    private $template;
    private $pageConf;
    
    public function __construct(StorageInterface $Storage, $PageConfig, Template\TemplateRendererInterface $Template = null)
    {
        $this->storage  = $Storage;
        $this->pageConf  = $PageConfig;
        $this->template  = $Template;
    }
    
    public function __invoke($Params, $Target)
    {
        switch ($Target) {
            
            // page related events
            case 'page::add-render.pre':
                $this->add_RenderPre($Params);
                break;
                
            case 'page::add-insert.pre':
                $this->add_InsertPre($Params);
                break;
                
            case 'page::add-insert.post':
                $this->add_InsertPost($Params);
                break;
              
            case 'page::edit-render.pre':
                $this->edit_RenderPre($Params);
                break;
            
            case 'page::edit-update.pre':
                $this->edit_UpdatePre($Params);
                break; 
                
            case 'page::cache-render.pre':
                $this->cache_RenderPre($Params);
                break;    
            
            // template related events    
            /*case 'page/template::add-render.pre':
                $this->templateAdd_RenderPre($Params);
                break;
            
            case 'page/template::add-insert.pre':
                $this->templateAdd_InsertPre($Params);
                break;
                
            case 'page/template::add-insert.post':
                $this->templateAdd_InsertPost($Params);
                break;
                
            case 'page/template::edit-render.pre':
                $this->templateEdit_RenderPre($Params);
                break;
                
            case 'page/template::edit-update.pre':
                $this->templateEdit_UpdatePre($Params);
                break;   */ 
            
            default:
                return;
                
        }
    }
    
    private function add_RenderPre($Params)
    {
        // change template
        $Params['template'] = 'templates/plugin/paginator/page::add';
    }
    
    private function add_InsertPre($Params)
    {
        unset($Params['data']['paginator']);
    }
    
    private function add_InsertPost($Params)
    {
        $this->edit_UpdatePre($Params);
    }
    
    private function edit_RenderPre($Params)
    {
        // change template
        $Params['template'] = 'templates/plugin/paginator/page::edit';
        
        // one template can have only one paginator either enabled or disabled
        $paginator = $this->storage->fetch('page_paginator', $Params['data']['page']['id'], 'pageId');
        
        if(!$paginator){
            $Params['data']['page']['paginator']['state'] = false;
            return;
        }
        
        // inject paginator settings from storage
        $Params['data']['page']['paginator'] = $paginator;
    }
    
    private function edit_UpdatePre($Params)
    {
        $paginator = $Params['data']['paginator'];
        unset($Params['data']['paginator']);
        
        $id = $Params['id'];
        
        // set authorId
        if(!empty($Params['data']['authorId'])){
            $paginator['authorId'] = $Params['data']['authorId'];            
        }
        
        // save paginator
        $this->save($id, $paginator);
    }
    
    private function cache_RenderPre($Params)
    {
        // var_dump($Params['data']);exit(__FILE__.'::'.__LINE__);
        
        // $paginator = $this->storage->fetch('page_paginator', $Params['data']['id'], 'pageId');
        $query = "  select page_paginator.*, page_templates.path as `page_templates.path` , page_templates.name as `page_templates.name`
                    from page_paginator 
                    inner join page_templates 
                        on page_paginator.listTemplateId = page_templates.id 
                    where page_paginator.pageId = :id";
        $select = $this->storage->query($query,[':id' => $Params['data']['id']]);
        if($select){
            $paginator = $select->fetch(\PDO::FETCH_ASSOC);
        }
        // var_dump($paginator);exit(__FILE__.'::'.__LINE__);
        
        if(!$paginator){
            return;
        }
        
        // based on paginator settings we get the list of pages
        $where = [
            'pages.state' => 1,
        ];
        
        if(!empty($paginator['sortBy'])){
            $sort = $paginator['sortBy'] . ' ' . $paginator['sortOrder'];
            $pages = $this->storage->fetchAllPages($where, $sort);
        }else{
            $pages = $this->storage->fetchAllPages($where);
        }
        
        $pages->setItemCountPerPage($paginator['itemsNumber']);
        
        $count = $pages->getTotalItemCount();
        $nrpages = ceil($count/$paginator['itemsNumber']);
        
        // alter the data to be used in the main page template
        $Params['data']['paginator'] = [];
        $Params['data']['paginator']['pages'] = $pages->getItemsByPage(1);
        $Params['data']['paginator']['count'] = $nrpages;
        // var_dump($Params['data']);exit(__FILE__.'::'.__LINE__);
        
        // if there is only one page then no need for sublists
        if($nrpages < 2){
            return;
        }
        
        $Params['data']['paginator']['next'] = $Params['data']['slug'].'/'.$paginator['name'].'-2'.'.html';
        
        // get sublists template
        $listTpl = 'templates'.$paginator['page_templates.path'].'::'.$paginator['page_templates.name'];
        
        // determine sublist dir
        $cachepath = getcwd().'/public/data/cache/html';
        $sublistdir = $cachepath . $Params['data']['path'].'/'. $Params['data']['slug'];
        // var_dump($sublistdir);exit(__FILE__.'::'.__LINE__);
        
        // create page dir if not exists
        if (!is_dir($sublistdir)) {
          // dir doesn't exist, make it
          mkdir($sublistdir, 0777, true);
        }
        
        // generate sublists
        for( $i=2; $i <= $nrpages; $i++ ){
            
            // get items per page
            $items = $pages->getItemsByPage($i);
            
            // generate content
            $listdata = [
                'page'       => $Params['data'],
                'pages'      => $items,
                'paginator'  => [], 
            ];
            
            // paginator prev
            if($i == 2){
                $listdata['paginator']['prev'] = '../'.$Params['data']['slug'].'.html';
            }else{
                $listdata['paginator']['prev'] = $paginator['name'].'-'. ($i-1) .'.html';
            }
            
            // paginator next
            if($i < $nrpages){
                $listdata['paginator']['next'] = $paginator['name'].'-'. ($i+1) .'.html';
            }
            
            $content = $this->template->render($listTpl, $listdata);
            
            // determine page name
            $pagename = '/'.$paginator['name'].'-'.$i.'.html';
            
            $file = $sublistdir.$pagename;
            if(file_put_contents($file, $content)){
                // do something nice
            }
            
        }
        
    }
    
    /*
    private function templateAdd_RenderPre($Params)
    {
        // change template
        $Params['template'] = 'templates/plugin/paginator/page/template::add';
    }
    
    private function templateAdd_InsertPre($Params)
    {
        unset($Params['data']['paginator']);
    }
    
    private function templateAdd_InsertPost($Params)
    {
        $this->templateEdit_UpdatePre($Params);
    }
    
    private function templateEdit_RenderPre($Params)
    {
        // change template
        $Params['template'] = 'templates/plugin/paginator/page/template::edit';
        
        // one template can have only one paginator either enabled or disabled
        $paginator = $this->storage->fetch('page_paginator', $Params['data']['page_template']['id'], 'templateId');
        
        if(!$paginator){
            $Params['data']['page_template']['paginator']['state'] = false;
            return;
        }
        
        // inject paginator settings from storage
        $Params['data']['page_template']['paginator'] = $paginator;
        
    }
    
    private function templateEdit_UpdatePre($Params)
    {
        $paginator = $Params['data']['paginator'];
        unset($Params['data']['paginator']);
        
        $id = $Params['id'];
        
        // save paginator
        $this->save($id, $paginator);
    }
    */
    
    private function save($id, $data)
    {
        
        // if disabled and not present yet we don't update anything
        if(!$data['state'] && empty($data['id'])){
            return;
        }
        
        $data['pageId'] = $id;
        
        // if id is present it means it exists then we must update
        if(!empty($data['id'])){
            $this->storage->update('page_paginator', $data, ['id' => $data['id']]);
            // on update regenerate all lists
            // $this->regenerate($data);
        }else{
            $this->storage->insert('page_paginator', $data);
        }
    }
    
    private function regenerate($data)
    {
        return;
        // get all pages that are active and use this template
        $where = [
            'pages.state' => 1,
            'pages.templateId' => $data['templateId']
        ];
        $pages = $this->storage->fetchAllPages($where);
        $cachepath = $this->pageConf['cache']['path'];
        
        foreach($pages as $page){
            
            var_dump($page);exit(__FILE__.'::'.__LINE__);
            $pagedir = $cachepath . $page['path'];
            if (!is_dir($pagedir)) {
                // the page dir should exist already
                continue;
                /*// dir doesn't exist, make it
                mkdir($pagedir, 0777, true);*/
            }
            $listdir = $pagedir.$page['slug'];
            // if lists dir not created then create it
            if (!is_dir($listdir)) {
                mkdir($listdir, 0777, true);
            }
            
        }
    }
    
}
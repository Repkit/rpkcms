<?php

namespace Plugin\Translator;

use Page\Storage\StorageInterface;
use Zend\Expressive\Template;

class Translator
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
            
            default:
                return;
                
        }
    }
    
    private function add_RenderPre($Params)
    {
        // change template
        \Plugin\Utils::changeTemplate($Params, 'templates/plugin/translator/page::add');
    }
    
    private function add_InsertPre($Params)
    {
        unset($Params['data']['translator']);
    }
    
    private function add_InsertPost($Params)
    {
        $this->edit_UpdatePre($Params);
    }
    
    private function edit_RenderPre($Params)
    {
        // change template
        \Plugin\Utils::changeTemplate($Params, 'templates/plugin/translator/page::edit');
        
        // one page can translate only one page (although db structure allow otherwise :P )
        $translator = $this->storage->fetch('page_translations', $Params['data']['page']['id'], 'translationPageId');
        
        if(!$translator){
            return;
        }
        
        // inject translator settings from storage
        $Params['data']['page']['translator'] = $translator;
    }
    
    private function edit_UpdatePre($Params)
    {
        $translator = $Params['data']['translator'];
        unset($Params['data']['translator']);
        
        $id = $Params['id'];
        
        // set authorId
        if(!empty($Params['data']['authorId'])){
            $translator['authorId'] = $Params['data']['authorId'];            
        }
        
        // save translator
        $this->save($id, $translator);
        
        // regenerate all lists
        // $this->regenerate();
    }
    
    private function save($id, $data)
    {
        // var_dump($data);exit(__FILE__.'::'.__LINE__);
           
        // if disabled and not present yet we don't update anything
        if(!$data['state'] && empty($data['id'])){
            return;
        }
        
        $data['translationPageId'] = $id;
        
        if(!empty($data['where'])){
            $conditions = $data['where'];
            unset($data['where']);
        }
        
        // if id is present it means it exists then we must update
        if(!empty($data['id'])){
            
            $this->storage->update('page_translations', $data, ['id' => $data['id']]);
            $translatorId = $data['id'];
            
            // on update regenerate all lists
            // $this->regenerate($data);
            
        }else{
            
            if(!isset($data['creationDate']) || empty($data['creationDate'])){
                $data['creationDate'] = date('Y-m-d H:i:s');
            }
            $translatorId = $this->storage->insert('page_translations', $data);
            
        }
    }
}
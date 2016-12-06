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
        
        $paginatorCondSql = " SELECT `id`, `field`, `operation`, `value`, `type`, `paginatorid`, `nestLevel` 
                        FROM `page_paginator_conditions` 
                        WHERE `paginatorid` = :paginatorid ";
        
        $paginatorConditions = $this->storage->query($paginatorCondSql,[':paginatorid' => $paginator['id']]);
        
        if($paginatorConditions){
            $paginator['where'] = $paginatorConditions->fetchAll(\PDO::FETCH_ASSOC);;
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
        
        // regenerate all lists
        $this->regenerate();
    }
    
    private function cache_RenderPre($Params)
    {
        // var_dump($Params['data']);exit(__FILE__.'::'.__LINE__);
        
        // $paginator = $this->storage->fetch('page_paginator', $Params['data']['id'], 'pageId');
        $query = "  select page_paginator.*, page_templates.path as `page_templates.path` , page_templates.name as `page_templates.name`
                    , page_paginator_conditions.field, page_paginator_conditions.operation, page_paginator_conditions.value
                    , count(page_paginator_conditions.id) as conditions
                    from page_paginator 
                    inner join page_templates 
                        on page_paginator.listTemplateId = page_templates.id 
                    left join page_paginator_conditions
	                    on page_paginator.id = page_paginator_conditions.paginatorId    
                    where page_paginator.pageId = :id
                    group by page_paginator_conditions.paginatorId";
                    
        $select = $this->storage->query($query,[':id' => $Params['data']['id']]);
        if($select){
            $paginator = $select->fetch(\PDO::FETCH_ASSOC);
        }
        // var_dump($paginator);exit(__FILE__.'::'.__LINE__);
        
        if(!$paginator){
            return;
        }
        
        // based on paginator settings we get the list of pages
        $where = 'WHERE pages.state = 1 AND pages.id != '.$Params['data']['id'];
        // if there are multiple conditions
        if($paginator['conditions'] > 1)
        {
            $condlevels = array();
            
            $condsql = " SELECT `id`, `field`, `operation`, `value`, `type`, `paginatorid`, `nestLevel` 
                        FROM `page_paginator_conditions` 
                        WHERE `paginatorid` = :paginatorid 
                        order by `nestLevel` asc "; 
            $conditions = $this->storage->query($condsql,[':paginatorid' => $paginator['id']]);
            
            if($conditions)
            {
                foreach($conditions as $index => $condition)
                {
                    // var_dump($condition['field']);exit(__FILE__.'::'.__LINE__);
                    $field = $condition['field'];
                    $operation = $this->operation($condition['operation']);
                    $opbegin = $operation['begin'];
                    $opend = $operation['end'];
                    $value = $condition['value'];
                    $lvl = $condition['nestLevel'];
                    $type = $condition['type'];
                    if(!isset($condlevels[$lvl]) || empty($condlevels[$lvl])){
                        $condlevels[$lvl] = array();
                    }
                    if(!isset($condlevels[$lvl][$type]) || empty($condlevels[$lvl][$type])){
                        $condlevels[$lvl][$type] = array();
                    }
                    $condlevels[$lvl][$type][] = " $field $opbegin $value $opend ";
                }
            }
            $condwhere = '';
            $multiplelevels = false;
            if(count($condlevels) > 1){
                $multiplelevels = true;
            }
            $defaultlvl = 0;
            // var_dump($condlevels);exit(__FILE__.'::'.__LINE__);
            foreach($condlevels as $lvl => $condlvl)
            {
                // var_dump($lvl, $condlvl);
                if($lvl > $defaultlvl)
                {
                    $condtypes = array_keys($condlvl);
                    $joincond = reset($condtypes);
                    $condwhere .= ' ' . $joincond . ' ';
                }
                $defaultlvl = $lvl;
                
                if($multiplelevels) {
                    $condwhere .= '(';
                }
                
                $iteration = 0;
                foreach($condlvl as $glue => $gluecond)
                {
                    // var_dump($glue, $gluecond);
                    if($lvl = $defaultlvl)
                    {
                        if($iteration > 0){
                            $condwhere .= $glue;
                        }else{
                            $iteration ++;
                        }
                    }
                    $condwhere .= implode($glue, $gluecond);
                }
                if($multiplelevels) {
                    $condwhere .= ')';
                }
                
            }
            // var_dump($condwhere);exit(__FILE__.'::'.__LINE__);
            $where .= " AND ($condwhere)"; 
        }
        elseif($paginator['conditions'] == 1)
        {
            $field = $paginator['field'];
            $operation = $this->operation($paginator['operation']);
            $opbegin = $operation['begin'];
            $opend = $operation['end'];
            $value = $paginator['value'];
            $where .= " AND $field $opbegin $value $opend"; 
        }
        // var_dump($where);exit(__FILE__.'::'.__LINE__);
        
        if(!empty($paginator['sortBy'])){
            $sort = $paginator['sortBy'] . ' ' . $paginator['sortOrder'];
        }else{
            $sort = 'pages.creationDate DESC';
        }
        
        $select = "CALL `fetchAllPages` (:where, :orderby, :offset, :limit); ";
        $selectparams = [':where' => $where, ':orderby' => $sort, ':offset' => 0, ':limit' => PHP_INT_MAX];
        // var_dump($selectparams);exit(__FILE__.'::'.__LINE__);
        
        $select = $this->storage->query($select, $selectparams);
        $pages = array();
        do {
            $pages = $select->fetchAll();
        } while ($select->nextRowset() && $select->columnCount());
        // var_dump($pages);exit(__FILE__.'::'.__LINE__);
        
        $count = count($pages);
        $nrpages = ceil($count/$paginator['itemsNumber']);
        
        // alter the data to be used in the main page template
        $Params['data']['paginator'] = [];
        $Params['data']['paginator']['pages'] = array_slice($pages, 0, $paginator['itemsNumber']);
        $Params['data']['paginator']['count'] = $nrpages;
        // var_dump($Params['data']);exit(__FILE__.'::'.__LINE__);
        
        // if there is only one page then no need for sublists
        if($nrpages < 2){
            return;
        }
        
        $Params['data']['paginator']['next'] = $Params['data']['slug'].'/'.$paginator['name'].'-2'.'.html';
        
        // get sublists template
        // $listTpl = 'templates'.$paginator['page_templates.path'].'::'.$paginator['page_templates.name'];
        $themepath = \Page\ModuleConfig::themepath(true);
        $listTpl = $themepath.$paginator['page_templates.path'].'::'.$paginator['page_templates.name'];
        
        // determine sublist dir
        // $cachepath = $this->pageConf['cache']['path'];
        $cachepath = \Page\ModuleConfig::cachepath();
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
            $items = array_slice($pages, $paginator['itemsNumber'] * ($i-1), $paginator['itemsNumber']);
            
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
        // var_dump($data);exit(__FILE__.'::'.__LINE__);
           
        // if disabled and not present yet we don't update anything
        if(!$data['state'] && empty($data['id'])){
            return;
        }
        
        $data['pageId'] = $id;
        
        if(!empty($data['where'])){
            $conditions = $data['where'];
            unset($data['where']);
        }
        
        // if id is present it means it exists then we must update
        if(!empty($data['id'])){
            
            $this->storage->update('page_paginator', $data, ['id' => $data['id']]);
            $paginatorId = $data['id'];
            
            // on update regenerate all lists
            // $this->regenerate($data);
            
        }else{
            
            if(!isset($data['creationDate']) || empty($data['creationDate'])){
                $data['creationDate'] = date('Y-m-d H:i:s');
            }
            $paginatorId = $this->storage->insert('page_paginator', $data);
            
        }
        
        if(!empty($conditions)){
            // save paginator conditions
            $this->saveConditions($paginatorId, $conditions);
            unset($conditions);
        }
    }
    
    private function regenerate()
    {
        $query = "  SELECT pages.slug, page_paginator.name, pagePathByCategoryId(pages.categoryid) as path 
                    FROM pages
                    INNER JOIN page_paginator 
                        ON pages.id = page_paginator.pageId ";
        $select = $this->storage->query($query);
        
        $lists = array();
        do {
            $lists = $select->fetchAll();
        } while ($select->nextRowset() && $select->columnCount());
        
        if(empty($lists)){
            return;
        }
        
        // $cachepath = $this->pageConf['cache']['path'];
        $cachepath = \Page\ModuleConfig::cachepath();
        
        foreach($lists as $list){
            
            $sublistdir = $cachepath . $list['path'].'/'. $list['slug'];
            
            // delete main list
            if(file_exists($sublistdir.'.html')){
                unlink($sublistdir.'.html');    
            }
            
            // delete sublists
            if(is_dir($sublistdir)){
                
                $name = $list['name'];
                $listpattern = "$sublistdir/$name-*.html";
                array_map('unlink', ( glob( $listpattern ) ? glob( $listpattern ) : array() ) );
                
            }
            
        }
    }
    
    private function saveConditions($Pid, $Conditions)
    {
        // if there are no modification marked by changed flag do nothing
        if(!isset($Conditions['changed']) || empty($Conditions['changed'])){
            return true;
        }
        unset($Conditions['changed']);
        
        $deleted = $this->storage->delete('page_paginator_conditions', ['paginatorId' => $Pid]);
        if(!$deleted){
            throw new \Exception('Could not delete old conditions');
        }
        
        try
        {
            
            // field
            if(!isset($Conditions['field']) || empty($Conditions['field'])){
                throw new \Exception('Field is required');
            }
            // operation
            if(!isset($Conditions['operation']) || empty($Conditions['operation'])){
                throw new \Exception('Operation is required');
            }
            // value
            if(!isset($Conditions['value']) || empty($Conditions['value'])){
                throw new \Exception('Value is required');
            }
            // type
            if(!isset($Conditions['type']) || empty($Conditions['type'])){
                throw new \Exception('Type is required');
            }
            // nestLevel
            if(!isset($Conditions['nestLevel']) || empty($Conditions['nestLevel'])){
                throw new \Exception('Nest level is required');
            }
        
        }
        catch(\Exception $e){
            // there is nothing to persist - maybe all conditions where removed
            return true;
        }
        
        $fields = $Conditions['field'];
        $operations = $Conditions['operation'];
        $values = $Conditions['value'];
        $types = $Conditions['type'];
        $levels = $Conditions['nestLevel'];
        
        if((count($fields) != count($operations)) || (count($operations) != count($values)) || (count($values) != count($types)) || (count($types) != count($levels))){
            throw new \Exception ('Count of elements differ!');
        }
        
        foreach($fields as $idx => $field)
        {
            $row = [];
            $row['field'] = $field;
            $row['operation'] = $operations[$idx];
            $row['value'] = $values[$idx];
            $row['type'] = $types[$idx];
            $row['paginatorId'] = $Pid;
            $row['nestLevel'] = $levels[$idx];
            
            $this->storage->insert('page_paginator_conditions', $row);
            
            unset($row);
        }
        
        return true;
    }
    
    /**
    * transform operation from text to operation
    * @param $operation string
    * return array('begin' => 'in (', 'end' => ')');
    */
    private function operation($operation)
    {
        switch ($operation) {
            case 'equalTo':
                return array('begin' => ' = ', 'end' => '');
                break;
            case 'notEqualTo':
                return array('begin' => ' != ', 'end' => '');
                break;
            case 'lessThanGreaterThan':
                return array('begin' => ' <> ', 'end' => '');
                break;
            case 'lessThanOrEqualTo':
                return array('begin' => ' <= ', 'end' => '');
                break;
            case 'greaterThanOrEqualTo':
                return array('begin' => ' >= ', 'end' => '');
                break;
            case 'like':
                return array('begin' => ' like ', 'end' => '');
                break;
            case 'notLike':
                return array('begin' => ' not like ', 'end' => '');
                break;
            case 'isNull':
                return array('begin' => ' is null ', 'end' => '');
                break;
            case 'isNotNull':
                return array('begin' => ' is not null ', 'end' => '');
                break;
            case 'in':
                return array('begin' => ' in (', 'end' => ')');
                break;
            case 'notIn':
                return array('begin' => ' not in (', 'end' => ')');
                break;
            case 'between':
                return array('begin' => ' between ', 'end' => '');
                break;
            case 'notBetween':
                return array('begin' => ' not between ', 'end' => '');
                break;
            
            default:
                throw new \Exception ("Operation $operation not supported");
                break;
        }
    }
    
}
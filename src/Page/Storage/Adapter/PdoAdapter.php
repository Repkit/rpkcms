<?php

namespace Page\Storage\Adapter;

use Zend\Paginator\Paginator;
use Page\Storage\StorageInterface;
use Page\Storage\Paginator\PdoPaginator;


class PdoAdapter implements StorageInterface
{
    private $pdo;

    public function __construct(\PDO $Pdo)
    {
        $this->pdo = $Pdo;
    }
    
    public function fetch($Name, $Value, $Key = 'id')
    {
        $select = $this->pdo->prepare("SELECT * from $Name WHERE $Key = :$Key");
        if (! $select->execute([":$Key" => $Value])) {
            return false;
        }
        
        return $select->fetch(\PDO::FETCH_ASSOC);
    }
    /*public function fetch($Id)
    {
        $select = $this->pdo->prepare('SELECT * from pages WHERE id = :id');
        if (! $select->execute([':id' => $Id])) {
            return false;
        }
        return $select->fetch();
    }*/
    
    public function fetchAll($Name, array $Where = ['state' => 1], $OrderBy = 'creationDate DESC')
    {
        // TODO: [SECURITY] - prepare statement for where
        if(!empty($Where)){
            $where = implode(' AND ', array_map(
               function ($k, $v) { return "$k = $v"; },
               array_keys($Where),
               array_values($Where)
            ));
            $where = 'WHERE '. $where;
        }else{
            $where = '';
        }
        
        $select = "SELECT * FROM $Name $where ORDER BY $OrderBy LIMIT :offset, :limit";
        $count  = "SELECT COUNT(id) FROM $Name $where";
        return $this->preparePaginator($select, $count);
    }
    /*public function fetchAll()
    {
        $select = 'SELECT * FROM pages WHERE state = 1 ORDER BY creationDate DESC LIMIT :offset, :limit';
        $count  = 'SELECT COUNT(id) FROM pages WHERE state = 1';
        return $this->preparePaginator($select, $count);
    }*/
    
    //insert query
	/*
	* syntax storage->insert(table name, insert data array, duplicated field checking array)
	* $inserted = $db->insert('users', array('email'=>'c@yahoo.com', 'nickname'=> 'Mr. C', 'password' => '159159'), array('email'));
	*/
    public function insert($Name, array $Data = [], array $Unique = []) 
    {
        $params = [];
		$values = [];
		
		//populating field array
		foreach($Data as $field => $value){
			$params[] = ':'. $field;
			// $values[':'.$field] = htmlentities($value);
			$values[':'.$field] = $value;
		}
		
		//generating field string
		$params = implode(',',$params);
		$fields = implode(',', array_keys($Data));
		
		// checking wheather same value exists or not
		/*$duplicate = false;
		if( count($Unique) > 0 ){
			$condition = [];
			foreach($Unique as $fieldName){
				$condition[] = $fieldName." = '".$Data[$fieldName]."' ";
			}
			$sql = "SELECT $Unique[0] FROM $Name WHERE ".implode('AND ',$condition);
			$res = $this->pdo->query($sql);
			
			//checking duplicate
			if( $res->rowCount() > 0 ) {
			    $duplicate = true;
			}
		}*/
		
		//processing insertsion while there is no duplicated value
		//if(!$duplicate) {
			$sql = 'INSERT INTO '.$Name.' ('.$fields.') VALUES('.$params.')';
			
			//query
			$insert = $this->pdo->prepare($sql);
			$insert->execute($values);
			
			// affected row
			//$affectedRow = $insert->rowCount();
			
			// last inseretd id
			//$lastInsertedId = $this->pdo->lastInsertId();
		//}
		
		// returning insert log
		return $this->pdo->lastInsertId();
 		//return array('affectedRow' => $affectedRow, 'insertedId' => $lastInsertedId, 'duplicate' => $duplicate);
    }
    
    public function update($Name, array $Data, array $Where)
    {
        $params = [];
		$values = [];
		
		//populating field array
		foreach($Data as $field => $value){
			$params[] = $field.' = :'.$field;
			// $values[':'.$field] = htmlentities($value);
			$values[':'.$field] = $value;
		}
		
		//generating field string
		$params = implode(',',$params);
		$fields = implode(',', array_keys($Data));
		
		//generating where condition
		// TODO: [SECURITY] - prepare statement for where
        $where = implode(' AND ', array_map(
           function ($k, $v) { return "$k = $v"; },
           array_keys($Where),
           array_values($Where)
        ));
        
    	$sql = 'UPDATE ' .$Name. ' SET ' .$params. ' WHERE ' . $where;
		
		//query
		$update = $this->pdo->prepare($sql);
		$update->execute($values);
		
		return $update->rowCount();
    }
    
    public function delete($Name, array $Where)
    {
        //generating where condition
		// TODO: [SECURITY] - prepare statement for where
        $where = implode(' AND ', array_map(
           function ($k, $v) { return "$k = $v"; },
           array_keys($Where),
           array_values($Where)
        ));
        
        $delete = $this->pdo->prepare("DELETE from $Name WHERE $where");
        if (! $delete->execute()) {
            return false;
        }
        
        return true;
    }
    
    public function pageBySlug($page)
    {
        $q = '  SELECT pages.*, pagePathByCategoryId(pages.categoryid) as path, page_templates.path as `page_templates.path` , page_templates.name as `page_templates.name` 
                FROM pages
                INNER JOIN page_templates 
                    ON pages.templateId = page_templates.id
                WHERE 
                    pages.state = 1
                    AND pages.slug = :slug';
        $select = $this->query($q, [':slug' => $page]);
        if($select){
            $select = $select->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $select;
    }
    
    public function pageById($page)
    {
        $q = '  SELECT pages.*, pagePathByCategoryId(pages.categoryid) as path, page_templates.path as `page_templates.path` , page_templates.name as `page_templates.name` 
                FROM pages
                INNER JOIN page_templates 
                    ON pages.templateId = page_templates.id
                WHERE 
                    pages.state = 1
                    AND pages.id = :id';
        $select = $this->query($q, [':id' => $page]);
        if($select){
            $select = $select->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $select;
    }
    
    public function fetchAllPages(array $Where = ['pages.state' => 1], $OrderBy = 'pages.creationDate DESC')
    {
        // TODO: [SECURITY] - prepare statement for where
        if(!empty($Where)){
            $where = implode(' AND ', array_map(
               function ($k, $v) { return "$k = $v"; },
               array_keys($Where),
               array_values($Where)
            ));
            $where = 'WHERE '. $where;
        }else{
            $where = '';
        }
        
        $select = "CALL `fetchAllPages` (:where, :orderby, :offset, :limit); ";
        $count  = "SELECT COUNT(id) FROM pages $where";
        
        $params = [':where' => $where, ':orderby' => $OrderBy];
                
        return $this->preparePaginator($select, $count, $params);
    }
    
    public function getPageCateoryPathById($Id)
    {
        $select  = $this->query("SELECT  `pagePathByCategoryId` ($Id) AS  `path` ; ");
        if($select){
            $select = $select->fetch(\PDO::FETCH_ASSOC);
        }
        
        return $select;
    }
    
    public function parents($Name, $Id = -1)
    {
        return $this->query("select * from $Name where id != :id1 and parentId < :id2", [':id1' => $Id,':id2' => $Id]);
    }
    
    public function query($Query, array $Params = [])
    {
        $query = $this->pdo->prepare($Query);
        if (! $query->execute($Params)) {
            return false;
        }
        
        return $query;
    }

    private function preparePaginator($select, $count, array $params = [])
    {
        $select = $this->pdo->prepare($select);
        $count  = $this->pdo->prepare($count);
        return new Paginator(new PdoPaginator(
            $select,
            $count,
            $params
        ));
    }
}
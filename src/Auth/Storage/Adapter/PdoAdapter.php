<?php

namespace Auth\Storage\Adapter;

use Zend\Paginator\Paginator;
use Auth\Storage\StorageInterface;
use Auth\Storage\Paginator\PdoPaginator;


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
    
    //insert query
	/*
	* syntax storage->insert(table name, insert data array, duplicated field checking array)
	* $inserted = $db->insert('users', array('email'=>'c@yahoo.com', 'nickname'=> 'Mr. C', 'password' => '159159'), array('email'));
	*/
    public function insert($Name, array $Data = [], array $Unique = []) 
    {
        
        $Data['creationDate'] = date('Y-m-d H:i:s');
        
        $params = [];
		$values = [];
		
		//populating field array
		foreach($Data as $field => $value){
			$params[] = ':'. $field;
			$values[':'.$field] = $value;
		}
		
		//generating field string
		$params = implode(',',$params);
		$fields = implode(',', array_keys($Data));
		
		$sql = 'INSERT INTO '.$Name.' ('.$fields.') VALUES('.$params.')';
		
		//query
		$insert = $this->pdo->prepare($sql);
		$insert->execute($values);
			
		// returning insert log
		return $this->pdo->lastInsertId();
    }
    
    public function update($Name, array $Data, array $Where)
    {
        $params = [];
		$values = [];
		
		//populating field array
		foreach($Data as $field => $value){
			$params[] = $field.' = :'.$field;
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
    
    public function insertMeta($Name, array $Data, array $Metadata, array $Metakeys = ['metaKey', 'metaValue']) 
    {
        
        $params = $values = $paramsMeta = [];
        
        foreach($Metakeys as $metaField){
            unset($Data[$metaField]);
        }
        
		$Data['creationDate'] = date('Y-m-d H:i:s');
		
		//generating field string
		$fields = implode(',', array_keys($Data));
		$fields .= ','. implode(',', $Metakeys);
		
		$n = 0;
		$metaKey = $Metakeys[0];
		$metaValue = $Metakeys[1];
        foreach ($Metadata as $key => $value) {
            $params[$n] = ' ( ';
            foreach($Data as $field => $val){
    			$params[$n] .= ":$field$n, ";
    			$values[":$field$n"] = $val;
    		}
            $params[$n] .= ":$metaKey$n, :$metaValue$n";
            $values["$metaKey$n"] = $key;
            $values["$metaValue$n"] = $value;
            $params[$n] .= ' ) ';
            $n++;
        }
		
		$sql = 'INSERT INTO '.$Name.' ('.$fields.') VALUES '.implode(',',$params);
		
		//query
		$insert = $this->pdo->prepare($sql);
		$insert->execute($values);
			
		// returning insert log
		return $this->pdo->lastInsertId();
    }
    
    
    public function userByEmail($Email)
    {
        $select = $this->pdo->prepare("SELECT users.*, user_roles.id as roleId, user_roles.name as role from users 
            inner join users_roles on users.id = users_roles.userId 
            inner join user_roles on users_roles.roleId = user_roles.id 
            WHERE users.email = :email");
        if (! $select->execute([":email" => $Email])) {
            return false;
        }
        
        return $select->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function userById($Id)
    {
        $select = $this->pdo->prepare("SELECT users.*, user_roles.id as roleId, user_roles.name as role from users 
            inner join users_roles on users.id = users_roles.userId 
            inner join user_roles on users_roles.roleId = user_roles.id 
            WHERE  users.id = :id");
        if (! $select->execute([":id" => $Id])) {
            return false;
        }
        
        return $select->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function parents($Name, $Id = -1)
    {
        return $this->query("select * from $Name where id != :id", [':id' => $Id]);
    }
    
    private function query($Query, array $Params = [])
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
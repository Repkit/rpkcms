<?php

namespace Auth\Storage\Adapter;

use PDO;

class PdoFactory
{
    public function __invoke($Services)
    {
        $config = $Services->get('config');
        $config = $config['auth']['storage'];
        
        //getting attributes if any
        $attributes = !empty($config["attributes"]) ? $config["attributes"] : array();
        
        //try getting connection string first
        $constr = !empty($config["connection_string"]) ? $config["connection_string"] : null;
        if(!$constr){
            
            //collecting config data
            $config = $config['connection_data'];
            
            $adapter     = $config["adapter"];
            $password    = !empty($config["password"]) ? $config["password"] : null;
            $username    = !empty($config["username"]) ? $config["username"] : null;
            $dsncfg = [
                "host"          => !empty($config["host"]) ? $config["host"] : null,
                "port"          => !empty($config["port"]) ? $config["port"] : 3306,
                "unix_socket"   => !empty($config["unix_socket"]) ? $config["unix_socket"] : null,
                "dbname"        => !empty($config["dbname"]) ? $config["dbname"] : null,
            ];
            
            foreach ($dsncfg as $cfg => $param) {
                if(!empty($cfg) && !empty($param)) {
                    $dsnparam[] = sprintf("%s=%s", $cfg, $param);
                }
            }
            
            $dsn = !empty($dsnparam) ? implode(';', $dsnparam) : "";
            $dsn = sprintf("%s:%s", $adapter, $dsn);
            
            $pdo = new PDO($dsn, $username, $password);
            
        }else{
            
            $pdo = new PDO($constr);
            
        }
        
        //setting attributes if any
        foreach($attributes as $attribute => $value){
            $pdo->setAttribute($attribute, $value);
        }
        
        return new PdoAdapter($pdo);
    }
}
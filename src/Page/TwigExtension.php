<?php

namespace Page;

use Twig_Extension;
use Twig_SimpleFunction;
use Twig_Extension_GlobalsInterface;
use Page\Storage\StorageInterface;

class TwigExtension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    
    private $storage;
    /**
     * @param ServerUrlHelper $serverUrlHelper
     * @param UrlHelper       $urlHelper
     * @param string          $assetsUrl
     * @param string          $assetsVersion
     * @param array           $globals
     */
    public function __construct(
        StorageInterface $Storage
    ) {
        $this->storage = $Storage;
    }
    
    public function getName()
    {
        return 'RpkPageTwigExtension';
    }
    
    /**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('pages', [$this, 'pages']
                // ,array(
                //     'is_safe' => array('html'),
                //     'needs_environment' => true
                // )
            ),
        ];
    }
    
    /**
     * Get all active 
     *
     * Usage: {{ path('article_show', {'id': '3'}) }}
     * Generates: /article/3
     *
     * @param null  $route
     * @param array $params
     *
     * @return string
     */
    public function pages($Offset = 1, $Limit = 10, array $Where = ['pages.state' => 1], $OrderBy = 'pages.creationDate DESC')
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
        
        $sqlstr = "CALL `fetchAllPages` (:where, :orderby, :offset, :limit); ";
        
        $params = [':where' => $where, ':orderby' => $OrderBy, ':offset' => $Offset, ':limit' => $Limit];
        
        $select = $this->storage->query($sqlstr, $params);
        
        // workaround for calling store procedures https://phpdelusions.net/pdo#call
        $data = array();
        do {
            $data = $select->fetchAll();
        } while ($select->nextRowset() && $select->columnCount());
        
        // var_dump($data);exit(__FILE__.'::'.__LINE__);
        return $data;
        
    }
}
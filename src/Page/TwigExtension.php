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
            new Twig_SimpleFunction('pageCategories', [$this, 'pageCategories']),
        ];
    }
    
    /**
     * Get all pages
     *
     * Usage: {{ pages(1, 10, {'pages.state': '1'}, 'pages.creationDate DESC') }}
     *
     * @param int  $Offset (optional) default: 1
     * @param int  $Limit (optional) default: 10
     * @param array $Where (optional) default: ['pages.state' => 1]
     * @param string $OrderBy (optional) default: 'pages.creationDate DESC'
     *
     * @return mixed
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
        
        // create generator for memory optimisation
        while ($data = $select->fetch(\PDO::FETCH_ASSOC)) {
            yield $data;
        }
        
    }
    
    /**
     * Get all categories
     *
     * Usage: {{ pages(1, 10, {'page_categories.state': '1'}, 'COUNT(1) DESC') }}
     *
     * @param int  $Offset (optional) default: 1
     * @param int  $Limit (optional) default: 10
     * @param array $Where (optional) default: ['pages.state' => 1]
     * @param string $OrderBy (optional) default: 'pages.creationDate DESC'
     *
     * @return mixed
     */
    public function pageCategories($Offset = 1, $Limit = 10, array $Where = ['page_categories.state' => 1], $OrderBy = 'COUNT(1) DESC')
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
        
        $sqlstr = "SELECT *, COUNT(1) FROM page_categories $where group by page_categories.id ORDER BY :orderby LIMIT :offset, :limit";
        
        $params = [':orderby' => $OrderBy, ':offset' => $Offset, ':limit' => $Limit];
        
        $select = $this->storage->query($sqlstr, $params);
        
        // create generator for memory optimisation
        while ($data = $select->fetch(\PDO::FETCH_ASSOC)) {
            yield $data;
        }
        
    }
    
}
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
    public function pages($Size = 10, $Page = 1, array $Where = ['pages.state' => 1], $OrderBy = 'pages.creationDate DESC')
    {
        $entities = $this->storage->fetchAllPages($Where, $OrderBy);
        $entities->setItemCountPerPage($Size);
        $entities->setCurrentPageNumber($Page);
        
        return $entities;
    }
}
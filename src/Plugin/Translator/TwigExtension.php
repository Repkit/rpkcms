<?php

namespace Plugin\Translator;

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
        return 'TranslatorTwigExtension';
    }
    
    /**
     * @return Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('translations', [$this, 'translations']),
        ];
    }
    
    /**
     * Get translations for a specific page
     * @return mixed
     */
    public function translations($Id)
    {
        
        $sqlstr = " SELECT pt.`translationPageId` as pageId, pt.`language`, p.`slug` FROM `page_translations` pt
                    INNER JOIN `pages` p ON pt.`translationPageId` = p.`id`
                    WHERE pt.`sourcePageId` = :id_a
                    
                    UNION ALL
                    
                    SELECT pt_a.`sourcePageId` as pageId, pt_a.`language`, p.`slug` FROM `page_translations` pt
                    INNER JOIN `page_translations` pt_a 
                        ON pt.`sourcePageId` = pt_a.`sourcePageId` 
                        AND pt_a.`translationPageId` is null 
                        AND pt_a.`sourcePageId` != :id_b
                    INNER JOIN `pages` p ON pt_a.`sourcePageId` = p.`id`
                    WHERE pt.`translationPageId` is null
                    
                    UNION ALL
                    
                    SELECT pt_a.`sourcePageId` as pageId, pt_a.`language`, p.`slug` FROM `page_translations` pt
                    INNER JOIN `page_translations` pt_a 
                        ON pt.`translationPageId` = pt_a.`sourcePageId` 
                        AND pt_a.`sourcePageId` != :id_c
                    INNER JOIN `pages` p ON pt_a.`sourcePageId` = p.`id`
                    WHERE pt.`sourcePageId` = :id_d
                    
                    UNION ALL
                    
                    SELECT pt_a.`translationPageId` as pageId, pt_a.`language`, p.`slug` FROM `page_translations` pt
                    INNER JOIN `page_translations` pt_a 
                        ON pt.`sourcePageId` = pt_a.`sourcePageId` 
                        AND pt_a.`translationPageId` is not null 
                        AND pt_a.`translationPageId` != :id_e
                    INNER JOIN `pages` p ON pt_a.`translationPageId` = p.`id`
                    WHERE pt.`translationPageId` = :id_f
                    ";
        
        $params = [':id_a' => $Id, ':id_b' => $Id, ':id_c' => $Id, ':id_d' => $Id, ':id_e' => $Id, ':id_f' => $Id];
        
        $select = $this->storage->query($sqlstr, $params);
        
        // create generator for memory optimisation
        while ($data = $select->fetch(\PDO::FETCH_ASSOC)) {
            yield $data;
        }
        
    }
    
}
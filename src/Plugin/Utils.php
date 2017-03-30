<?php

namespace Plugin;

class Utils
{
    public static function changeTemplate($Params, $Template)
    {
        $Params['data']['__template__'][static::prepareTemplate($Template)] = static::prepareTemplate($Params['template']);
        $Params['template'] = $Template;
        
        // var_dump($Params['data']['__template__']);exit(__FILE__.'::'.__LINE__);
    }
    
    public static function prepareTemplate($Template)
    {
        $template = str_replace('::','/',$Template);
        $template = str_replace(':','/',$template);
        return '@'.$template.'.html.twig';
    }
}

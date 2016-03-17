<?php

namespace Auth\Storage\Mapper;


class Google implements MapperInterface
{
    
    public static function map(array $Data)
    {
        $user = $userMeta = [];
        $user['username']    = $Data['raw']['name'];
        $user['password']    = $Data['credentials']['token']; //this is not really used:)
        $user['email']       = $Data['raw']['email'];
        $user['nickname']    = $Data['raw']['given_name'];
        
        $userMeta['externalId']     = $Data['uid'];
        $userMeta['first_name']     = $Data['info']['first_name'];
        $userMeta['last_name']      = $Data['info']['last_name'];
        $userMeta['image']          = $Data['info']['image'];
        $userMeta['verified_email'] = $Data['raw']['verified_email'];
        $userMeta['name']           = $Data['raw']['name'];
        $userMeta['given_name']     = $Data['raw']['given_name'];
        $userMeta['family_name']    = $Data['raw']['family_name'];
        $userMeta['link']           = $Data['raw']['link'];
        $userMeta['picture']        = $Data['raw']['picture'];
        $userMeta['locale']         = $Data['raw']['locale'];
        
        return ['users' => $user, 'user_meta' => $userMeta];
    }
    
    public static function unmap(array $Data)
    {
        
    }
}
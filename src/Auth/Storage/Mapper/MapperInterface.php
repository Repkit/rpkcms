<?php

namespace Auth\Storage\Mapper;

interface MapperInterface
{
    /**
     *  map
     *  @param array $Data
     *  @return array
     */ 
    public static function map(array $Data);
    
    /**
     *  unmap
     *  @param array $Data
     *  @return array
     */ 
    public static function unmap(array $Data);
}
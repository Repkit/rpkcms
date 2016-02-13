<?php

namespace Page\Storage;

interface StorageInterface
{
    public function fetch($Id);
    
    public function fetchBySlug($Slug);
    
    public function fetchAll();
}
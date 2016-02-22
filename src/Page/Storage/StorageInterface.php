<?php

namespace Page\Storage;

interface StorageInterface
{
    public function fetch($Name, $Value);
    
    public function fetchAll($Name);
}
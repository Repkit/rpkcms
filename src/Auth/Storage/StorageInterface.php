<?php

namespace Auth\Storage;

interface StorageInterface
{
    public function fetch($Name, $Value);
    
    public function fetchAll($Name);
}
<?php

namespace Page\Storage;

/**
 * Abstract class for pagination adapters.
 */
abstract class AbstractPaginator implements PaginatorInterface
{
    abstract public function getItems($offset, $itemCountPerPage);
}
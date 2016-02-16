<?php
namespace Page\Storage\Paginator;

use PDO;
use PDOStatement;
use Zend\Paginator\Adapter\AdapterInterface;
use Page\Storage\AbstractPaginator;
use Page\Storage\StorageException;

class PdoPaginator extends AbstractPaginator implements AdapterInterface
{
    protected $count;
    protected $params;
    protected $select;

    public function __construct(PdoStatement $select, PdoStatement $count, array $params = [])
    {
        $this->select = $select;
        $this->count  = $count;
        $this->params = $params;
    }

    public function getItems($offset, $itemCountPerPage)
    {
        $params = array_merge($this->params, [
            ':offset' => $offset,
            ':limit'  => $itemCountPerPage,
        ]);

        $result = $this->select->execute($params);

        if (! $result) {
            throw new StorageException('Failed to fetch items from database');
        }

        return $this->select->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count()
    {
        $result = $this->count->execute($this->params);
        if (! $result) {
            throw new StorageException('Failed to fetch count from database');
        }
        return $this->count->fetchColumn();
    }
}

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
        
        // create generator for memory optimisation
        while ($data = $this->select->fetch(\PDO::FETCH_ASSOC)) {
            yield $data;
        }
        
        // workaround for calling store procedures https://phpdelusions.net/pdo#call
        /*$data = array();
        do {
            $data = $this->select->fetchAll();
        } while ($this->select->nextRowset() && $this->select->columnCount());
        
        return $data;*/
    }

    public function count()
    {
        /* we don't need params for geting count */
        // $result = $this->count->execute($this->params);
        $result = $this->count->execute();
        if (! $result) {
            throw new StorageException('Failed to fetch count from database');
        }
        return $this->count->fetchColumn();
    }
}

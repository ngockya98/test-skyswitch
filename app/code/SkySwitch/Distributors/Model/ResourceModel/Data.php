<?php

namespace SkySwitch\Distributors\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class Data
{
    /**
     * @var ResourceConnection
     */
    private $resource_connection;

    /**
     * @param ResourceConnection $resource_connection
     */
    public function __construct(
        ResourceConnection $resource_connection
    ) {
        $this->resource_connection = $resource_connection;

        //Initiate Connection
        $this->connection = $this->resource_connection->getConnection();
    }

    /**
     * Fetch data using custom select query
     *
     * @param string $table_name
     * @param array|mixed $wheres
     * @param array|mixed $bindings
     * @return mixed
     */
    public function selectQuery($table_name, $wheres = [], $bindings = [])
    {
        $select = $this->connection->select()
            ->from(
                [$table_name => $table_name],
                ['*']
            );

        foreach ($wheres as $where) {
            $select = $select->where($where);
        }

        $records = $this->connection->fetchAll($select, $bindings);

        return $records;
    }

    /**
     * Insert data using custom query
     *
     * @param string $table_name
     * @param mixed $data
     * @return void
     */
    public function insert($table_name, $data)
    {
        $this->connection->insert($table_name, $data);
    }

    /**
     * Update data using custom query
     *
     * @param string $table_name
     * @param mixed $data
     * @param string|mixed $where
     * @return void
     */
    public function update($table_name, $data, $where)
    {
        $this->connection->update($table_name, $data, $where);
    }

    /**
     * Delete data using custom query
     *
     * @param string $table_name
     * @param string|mixed $wheres
     * @return void
     */
    public function delete($table_name, $wheres)
    {
        $where_conditions = [];

        foreach ($wheres as $where) {
            $where_conditions[] = $this->connection->quoteInto($where['condition'], $where['binding']);
        }
        $this->connection->delete($table_name, $where_conditions);
    }
}

<?php

class TDBOMySQL extends TSQLBase
{
    const string TABLE_NAME = '';
    const array TABLE_FUNCTIONS  = [];
    const array TABLE_CONDITIONS = [];
    const array TABLE_KEYS       = [];

    protected TMySQL $db;

    final public static function getInstance(TMySQL $db)
    {
        static $instances = [];

        $calledClass = get_called_class();

        if (!isset($instances[$calledClass]))
            $instances[$calledClass] = new $calledClass($db);
        else
            $instances[$calledClass]->db = $db;

        return $instances[$calledClass];
    }

    public function __construct(TMySQL $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @throws ESQLBase
     */
    public function count(array $where_params = [], $column = null, ?string $from = null): mixed
    {
        $params = [];
        $turn = 1;

        $where = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($column === null)
            $column = static::TABLE_KEYS[0];

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_count($column, $where, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

      //  parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);

        $stmt->bind($params);
        $stmt->execute();

        $row = $stmt->fetch();

        return (int)($row['pocet'] ?? 0);
    }

    public function delete(array $where_params, ?string $from = null): TMySQLStatement
    {
        $params = [];
        $turn = 1;

        $where = parent::parse_where(
            $where_params,
            $params,
            static::TABLE_COLUMNS,
            static::TABLE_FUNCTIONS,
            static::TABLE_CONDITIONS,
            $this->LOGIC,
            $turn
        );

        if ($from === null) {
            $from = static::TABLE_NAME;
        }

        $sql = parent::_delete($where, $from);

     //   parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!empty($params)) {
            $stmt->bind($params); // tvůj wrapper
        }

        if (!$stmt->execute(false)) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt;
    }

    /**
     * @throws ESQLBase
     * @throws EDBOMySQL
     */
    public function select(mixed $columns, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): TMySQLStatement
    {
        $params = [];
        $turn = 1;

        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_select($columns, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

     //   parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        if($stmt)
        {
            $stmt->bind($params);
            $stmt->execute();
            return $stmt;
        } else
            throw new EDBOMySQL('Exception in SQL query', -230);
    }

    public function group($columns, $group, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): TMySQLStatement
    {
        $params = [];
        $turn = 1;

        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_group($columns, $group, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

     //   parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        if($stmt)
        {
            $stmt->bind($params);
            $stmt->execute();
            return $stmt;
        } else
            throw new EDBOMySQL('SQL SELECT GROUP BY: Bad SQL query!');
    }

    /**
     * @throws ESQLBase
     */
    public function column(string $column, array $where_params = [], array $order = [], ?string $from = null): mixed
    {
        $params = [];
        $turn = 1;

        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_column($column, $where, $order, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

     //   parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->bind($params);

        $stmt->execute();
        $row = $stmt->fetchNum();

        return $row[0];
    }

    /**
     * @throws ESQLBase
     */
    public function record($columns, array $where_params, ?string $from = null): ?array
    {
        $params = [];
        $turn = 1;

        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_select($columns, $where, [], 0, 1, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

     //   parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->bind($params);

        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * @throws ESQLBase
     */
    public function insert(array $insert_params): TMySQLStatement
    {
        $columns = '';
        $params = [];

        $values = parent::parse_insert($insert_params, $columns, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        $sql = parent::_insert($columns, $values, static::TABLE_NAME);

     //   parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->bind($params);

        $stmt->execute();
        return $stmt;
    }

    /**
     * @throws ESQLBase
     */
    public function insertMulti(array $multi_insert_params): TMySQLStatement
    {
        if (empty($multi_insert_params)) {
            return 0;
        }

        $columns = '';
        $params  = [];
        $sql     = '';

        foreach ($multi_insert_params as $m => $insert_params)
        {
            $values = parent::parse_insert(
                $insert_params,
                $columns,
                $params,
                static::TABLE_COLUMNS,
                static::TABLE_FUNCTIONS,
                $m
            );

            if ($sql === '') {
                $sql = 'INSERT INTO ' . static::TABLE_NAME . ' (' . $columns . ') VALUES';
            } else {
                $sql .= ',';
            }

            $sql .= '(' . $values . ')';
        }

      //  parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->bind($params);
        $stmt->execute();

        return $stmt;
    }

    /**
     * @throws ESQLBase
     */
    public function update(array $update_params, array $where_params): TMySQLStatement
    {
        $params  = [];
        $turn = 1;

        $set = parent::parse_update(
            $update_params,
            $params,
            static::TABLE_COLUMNS,
            static::TABLE_FUNCTIONS
        );

        $where = parent::parse_where(
            $where_params,
            $params,
            static::TABLE_COLUMNS,
            static::TABLE_FUNCTIONS,
            static::TABLE_CONDITIONS,
            $this->LOGIC,
            $turn
        );

        $sql = parent::_update($set, $where, static::TABLE_NAME);

    //    parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->bind($params);
        $stmt->execute();

        return $stmt;
    }

    /**
     * @throws ESQLBase
     */
    public function options(array $columns, array $options = [], array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): array
    {
        if(count($options) > 0)
        {
            $where_zal = [];
            foreach($options as $column => $values)
            {
                if((string)$column != '')
                {
                    if((string)$values != '')
                    {
                        if(is_array($values) || is_numeric($values))
                        {
                            unset($options[$column]);
                            $where_zal[$column] = $values;
                        }
                    } else
                        unset($options[$column]);
                }
            }

            if(count($where_zal) > 0)
                if(count($where_params) > 0)
                    $where_params = ['ORx'=>['ANDx'=>$where_params, 'ORx'=>$where_zal]];
        }

        $params  = [];
        $turn = 1;
        $where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        $column_id = key($columns);
        $column_name = current($columns);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_options($column_id, $column_name, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

    //    parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->bind($params);

        $stmt->execute();

        if(is_array($column_name))
        {
            while($zaznam = $stmt->fetch())
            {
                $name = '';
                foreach($column_name as $col)
                {
                    if($name != '')
                        $name .= ' ';

                    $name .= $zaznam[$col];
                }

                $options[$zaznam[$column_id]] = $name;
            }
        } else
            while($zaznam = $stmt->fetch())
                $options[$zaznam[$column_id]] = $zaznam[$column_name];

        return $options;
    }

    /**
     * @throws ESQLBase
     */
    public function hash(array $columns, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): array
    {
        $params  = [];
        $turn = 1;

        $where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        $column_id = key($columns);
        $column_values = current($columns);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_hash($column_id, $column_values, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

    //    parent::logger($sql, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->bind($params);

        $stmt->execute();

        $result = [];
        while($zaznam = $stmt->fetch())
            $result[$zaznam[$column_id]] = $zaznam;

        return $result;
    }
}

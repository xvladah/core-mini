<?php

class TDBOSQLite extends TSQLBase
{
    const string HOUR_FORMAT					= '%H';

    const string TIME_FORMAT					= '%H:%M';
    const string TIME_FORMAT_LONG				= '%H:%M:%S';

    const string DATE_FORMAT					= '%Y-%m-%d';
    const string DATE_FORMAT_CZ				= '%d.%m.%Y';

    const string DATETIME_FORMAT				= '%Y-%m-%d %H:%M';
    const string DATETIME_FORMAT_CZ			= '%d.%m.%Y %H:%M';

    const string DATETIME_FORMAT_LONG			= '%Y-%m-%d %H:%M:%S';
    const string DATETIME_FORMAT_LONG_CZ		= '%d.%m.%Y %H:%M:%S';

    const string MONTH_FORMAT					= '%m';

    const string YEAR_FORMAT   				= '%Y';
    const string YEAR_MONTH_FORMAT 			= '%Y-%m';
    const string YEAR_MONTH_FORMAT_CZ 			= '%m.%Y';

    const int ERROR_DIR_NOT_EXISTS 			= -1;
    const int ERROR_DIR_NOT_CREATED 		= -11;
    const int ERROR_FILE_NOT_EXISTS			= -110;
    const int ERROR_FILE_NOT_CREATED		= -111;

    const int INFO_DIR_EXISTS				= 1;
    const int INFO_DIR_CREATED				= 11;
    const int INFO_FILE_EXISTS				= 110;
    const int INFO_FILE_CREATED				= 111;

    const array TABLE_KEYS        = [];
    const array TABLE_FUNCTIONS   = [];
    const array TABLE_CONDITIONS  = [];
    const string TABLE_NAME        = '';

    protected SQLite3 $sqlite;

    final public static function getInstance(SQLite3 $sqlite)
    {
        static $instances = [];

        $calledClass = get_called_class();

        if (!isset($instances[$calledClass]))
            $instances[$calledClass] = new $calledClass($sqlite);
        else
            $instances[$calledClass]->sqlite = $sqlite;

        return $instances[$calledClass];
    }

    public function __construct(SQLite3 $sqlite)
    {
        $this->sqlite = $sqlite;
        return $this;
    }

    /**
     * Funkce zalozi adresar
     *
     * @param string $directory
     * @param int $attr
     * @return mixed
     */
    public static function createDirectory(string $directory, int $attr = 0777) :int
    {
        if(!file_exists($directory))
        {
            $success = mkdir($directory);
            if(!$success)
                return self::ERROR_DIR_NOT_CREATED;
            else {
                chmod($directory, $attr);
                $result = self::INFO_DIR_CREATED;
            }
        } else
            $result = self::INFO_DIR_EXISTS;

        return $result;
    }

    public static function getDirectory(string $basedir, string $subdir) :string
    {
        return $basedir . '/' . $subdir;
    }

    public static function existsDirectory(string $directory) :int
    {
        if(!file_exists($directory))
            $result = self::ERROR_DIR_NOT_EXISTS;
        else
            $result = self::INFO_DIR_EXISTS;

        return $result;
    }

    public static function getDatabaseFile(string $directory, string $filename) :string
    {
        return $directory.'/'.basename($filename);
    }

    public  static function existsDatabaseFile(string $directory, string $filename) :int
    {
        if(!file_exists(self::getDatabaseFile($directory, $filename)))
            return self::ERROR_FILE_NOT_EXISTS;
        else
            return self::INFO_FILE_EXISTS;
    }

    public static function createDatabaseFile(string $directory, string $filename, string $template) :int
    {
        $result = self::createDirectory($directory);
        if($result > 0)
        {
            $file = self::getDatabaseFile($directory, $filename);
            if(!file_exists($file))
            {
                if(copy($template, $file))
                {
                    chmod($file, 0777);
                    $result = self::INFO_FILE_CREATED;
                } else
                    return self::ERROR_FILE_NOT_CREATED;
            } else
                $result = self::INFO_FILE_EXISTS;
        }

        return $result;
    }


    /**
     * @throws ESQLBase
     */
    public function count(array $where_params = [], ?string $column = null, ?string $from = null)
    {
        $params = [];
        $turn = 1;
        $where = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($column === null)
            $column = static::TABLE_KEYS[0];

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_count($column, $where, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query 	= $stmt->execute();
        if($query)
            $row 	= $query->fetchArray(SQLITE3_NUM);
        else
            throw new Exception('['.$this->sqlite->lastErrorCode().'] '.$this->sqlite->lastErrorMsg(), -100);

        return $row[0];
    }

    /**
     * @throws ESQLBase
     */
    public function countDistinct(array $where_params = [], ?string $column = null, ?string $from = null)
    {
        $params = [];
        $turn = 1;
        $where = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($column === null)
            $column = static::TABLE_KEYS[0];

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_countDistinct($column, $where, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query 	= $stmt->execute();
        if($query)
            $row 	= $query->fetchArray(SQLITE3_NUM);
        else
            throw new Exception('['.$this->sqlite->lastErrorCode().'] '.$this->sqlite->lastErrorMsg(), -1 * E_USER_ERROR);

        return $row[0];
    }

    public function delete(array $where_params, ?string $from = null): false|SQLite3Result
    {
        $params = [];
        $turn = 1;
        $where = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_delete($where, $from);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        return $stmt->execute();
    }

    public function select(mixed $columns, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): false|SQLite3Result
    {
        $params = [];
        $turn = 1;
        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_select($columns, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        if($stmt = $this->sqlite->prepare($sql))
        {
            foreach($params as $sql_param => $value)
                $stmt->bindValue($sql_param, $value);

            return $stmt->execute();
        } else
            throw new ESQLiteException('Exception in SQL query', -200);
    }

    public function group(mixed $columns, $group, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): false|SQLite3Result
    {
        $params = [];
        $turn = 1;
        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_group($columns, $group, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        if($stmt = $this->sqlite->prepare($sql))
        {
            foreach($params as $sql_param => $value)
                $stmt->bindValue($sql_param, $value);

            return $stmt->execute();
        } else
            throw new ESQLiteException('SQL SELECT GROUP BY: Bad SQL query!', -210);
    }

    /**
     * @throws ESQLBase
     */
    public function column(string $column, array $where_params, array $order = [], ?string $from = null): mixed
    {
        $params = [];
        $turn = 1;
        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_column($column, $where, $order, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->repare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query 	= $stmt->execute();
        if($query)
            $row 	= $query->fetchArray(SQLITE3_NUM);
        else
            throw new Exception('['.$this->sqlite->lastErrorCode().'] '.$this->sqlite->lastErrorMsg(), -1 * E_USER_ERROR);

        return $row[0];
    }

    public function record($columns, array $where_params, ?string $from = null): false|array
    {
        $params = [];
        $turn = 1;
        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_select($columns, $where, [], 0, 1, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query = $stmt->execute();
        if($query)
            return $query->fetchArray(SQLITE3_ASSOC);
        else
            throw new Exception('['.$this->sqlite->lastErrorCode().'] '.$this->sqlite->lastErrorMsg(), -1 * E_USER_ERROR);
    }

    public function insert(array $insert_params): false|SQLite3Result
    {
        $columns = '';
        $params = [];
        $values = parent::parse_insert($insert_params, $columns, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        $sql = parent::_insert($columns, $values, static::TABLE_NAME);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        if($stmt !== false)
        {
            foreach($params as $sql_param => $value)
                $stmt->bindValue($sql_param, $value);
        } else
            throw new ESQLiteException('Exception during insert values!'.$sql.print_r($params, true).$this->sqlite->lastErrorMsg(), -230);

        $query = $stmt->execute();
        return $query;
    }

    public function insertMulti(array $multi_insert_params): false|SQLite3Result
    {
        $columns 	= '';
        $params 	= [];
        $sql 		= '';

        foreach($multi_insert_params as $m => $insert_params)
        {
            $values = parent::parse_insert($insert_params, $columns, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, $m);
            if($sql == '')
                $sql = 'INSERT INTO '.static::TABLE_NAME.'('.$columns.')VALUES';
            else
                $sql .= ',';

            $sql .= '('.$values.')';
        }
        $sql .= ';';

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        if($stmt !== false)
        {
            foreach($params as $sql_param => $value)
                $stmt->bindValue($sql_param, $value);
        } else
            throw new ESQLiteException('Exception during insert values!'.$sql.print_r($params, true).$this->sqlite->lastErrorMsg(), -235);

        $query = $stmt->execute();
        return $query;

    }

    public function update(array $update_params, array $where_params): false|SQLite3Result
    {
        $params  = [];
        $turn = 1;
        $where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);
        $set 	 = parent::parse_update($update_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        $sql = parent::_update($set, $where, static::TABLE_NAME);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query = $stmt->execute();
        return $query;
    }

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

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query = $stmt->execute();

        if(is_array($column_name))
        {
            while($zaznam = $query->fetchArray(SQLITE3_ASSOC))
            {
                $name = '';
                foreach($column_name as $col)
                {
                    if($name != '')$name .= ' ';
                    $name .= $zaznam[$col];
                }

                $options[$zaznam[$column_id]] = $name;
            }
        } else
            while($zaznam = $query->fetchArray(SQLITE3_ASSOC))
                $options[$zaznam[$column_id]] = $zaznam[$column_name];

        return $options;
    }

    public function hash(array $columns, array $where_params = [], array $order = [], int $offset = 0, int $count= 0, ?string $from = null): array
    {
        $params  = [];
        $turn = 1;
        $where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        $column_id = key($columns);
        $column_values = current($columns);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_hash($column_id, $column_values, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query = $stmt->execute();

        $result = [];
        while($zaznam = $query->fetchArray(SQLITE3_ASSOC))
            $result[$zaznam[$column_id]] = $zaznam;

        return $result;
    }

    public function array(string $column, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): array
    {
        $params  = [];
        $turn = 1;
        $where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_array($column, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query = $stmt->execute();

        $result = [];
        while($zaznam = $query->fetch())
            $result[] = $zaznam[$column];

        return $result;
    }

    public function arrayDistinct(string $column, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): array
    {
        $params  = [];
        $turn = 1;
        $where	 = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_arrayDistinct($column, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);
        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query = $stmt->execute();

        $result = [];
        while($zaznam = $query->fetch())
            $result[] = $zaznam[$column];

        return $result;
    }

    public function maxColumn(?string $column = null, array $where_params = [], ?string $from = null) :int
    {
        $params	= [];
        $turn = 1;
        $where	= parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($column === null)
            $column = static::TABLE_KEYS[0];

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = 'SELECT CASE WHEN MAX('.static::TABLE_COLUMNS[$column]['column'].')IS NULL THEN 0 ELSE MAX('.static::TABLE_COLUMNS[$column]['column'].') END'.
            parent::_from($from).
            parent::_where($where);

        parent::logger($sql, $params);

        $stmt = $this->sqlite->prepare($sql);

        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query 	= $stmt->execute();
        $row 	= $query->fetchArray(SQLITE3_NUM);

        return $row[0];
    }

    public function nextColumn(?string $column = null, array $where_params = [], ?string $from = null) :int
    {
        $params	= [];
        $turn = 1;
        $where	= parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($column === null)
            $column = static::TABLE_KEYS[0];

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = 'SELECT IF(MAX('.static::TABLE_COLUMNS[$column]['column'].')IS NULL,1,MAX('.static::TABLE_COLUMNS[$column]['column'].')+1)'.
            parent::_from($from).
            parent::_where($where);

        parent::logger($sql, $params);

        $stmt = $this->slite->prepare($sql);

        foreach($params as $sql_param => $value)
            $stmt->bindValue($sql_param, $value);

        $query 	= $stmt->execute();
        $row 	= $query->fetchArray(SQLITE3_NUM);

        return $row[0];
    }

    public function setAutoIncrement(int $auto_increment): bool
    {
        $sql = 'UPDATE SQLITE_SEQUENCE SET seq='.$auto_increment.' WHERE name="'.static::TABLE_NAME.'"';

        parent::logger($sql);

        $result = $this->sqlite->exec($sql);

        if($result !== false)
            return $result;
        else
            throw new ESQLiteException('Auto increment set failed', -240);
    }

}


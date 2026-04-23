<?php

class TDBOOCI8 extends TSQLBase
{
    const string HOUR_FORMAT              = 'HH24';

    const string TIME_FORMAT              = 'HH24:MI';
    const string TIME_FORMAT_LONG         = 'HH24:MI:SS';

    const string DATE_FORMAT              = 'YYYY-MM-DD';
    const string DATE_FORMAT_CZ           = 'DD.MM.YYYY';

    const string DATETIME_FORMAT          = 'YYYY-MM-DD HH24:MI';
    const string DATETIME_FORMAT_CZ       = 'DD.MM.YYYY HH24:MI';

    const string DATETIME_FORMAT_LONG     = 'YYYY-MM-DD HH24:MI:SS';
    const string DATETIME_FORMAT_LONG_CZ  = 'DD.MM.YYYY HH24:MI:SS';

    const string MONTH_FORMAT             = 'MM';
    const string YEAR_FORMAT              = 'YYYY';

    const string YEAR_MONTH_FORMAT        = 'YYYY-MM';
    const string YEAR_MONTH_FORMAT_CZ     = 'MM.YYYY';

    const array TABLE_KEYS        = [];
    const array TABLE_FUNCTIONS   = [];
    const array TABLE_CONDITIONS  = [];
    const string TABLE_NAME       = '';

    protected $oci; // OCI8 connection wrapper (TPDO OCI / statement factory)

    final public static function getInstance($oci)
    {
        static $instances = [];

        $calledClass = get_called_class();

        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass($oci);
        } else {
            $instances[$calledClass]->oci = $oci;
        }

        return $instances[$calledClass];
    }

    public function __construct($oci)
    {
        if (!$oci instanceof TOCI8) {
            throw new RuntimeException(
                'Only TOCI8 driver is allowed for TDBOOCI8 in constructor'
            );
        }

        static::$NAMED_PIPES = true;

        $this->oci = $oci;
    }

    /* =========================
     * CORE EXECUTION WRAPPER
     * ========================= */

    private function prepare(string $sql)
    {
        $stmt = $this->oci->query($sql);

        if (!$stmt) {
            throw new Exception("OCI prepare failed");
        }

        return $stmt;
    }

    private function bindParams($stmt, array $params)
    {
        foreach ($params as $k => $v) {
            $stmt->bind($k, $v);
        }
    }

    private function execute($stmt)
    {
        return $stmt->execute();
    }

    private function fetch($stmt)
    {
        return $stmt->fetch();
    }

    /* =========================
     * COUNT
     * ========================= */

    public function count(array $where_params = [], ?string $column = null, ?string $from = null)
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

        if ($column === null) {
            $column = static::TABLE_KEYS[0];
        }

        if ($from === null) {
            $from = static::TABLE_NAME;
        }

        $sql = parent::_count(
            $column,
            $where,
            $from,
            static::TABLE_COLUMNS,
            static::TABLE_FUNCTIONS
        );

        parent::logger($sql, $params);

        $stmt = $this->prepare($sql);
        $this->bindParams($stmt, $params);

        $stmt->execute();
        $row = $stmt->fetch();

        return (int)$row[0];
    }

    /* =========================
     * COUNT DISTINCT
     * ========================= */

    public function countDistinct(array $where_params = [], ?string $column = null, ?string $from = null)
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

        if ($column === null) {
            $column = static::TABLE_KEYS[0];
        }

        if ($from === null) {
            $from = static::TABLE_NAME;
        }

        $sql = parent::_countDistinct(
            $column,
            $where,
            $from,
            static::TABLE_COLUMNS,
            static::TABLE_FUNCTIONS
        );

        parent::logger($sql, $params);

        $stmt = $this->prepare($sql);
        $this->bindParams($stmt, $params);

        $stmt->execute();
        $row = $stmt->fetch();

        return (int)$row[0];
    }

    /* =========================
     * SELECT
     * ========================= */

    public function select($columns, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null)
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

        $sql = parent::_select(
            $columns,
            $where,
            $order,
            $offset,
            $count,
            $from,
            static::TABLE_COLUMNS,
            static::TABLE_FUNCTIONS
        );

        parent::logger($sql, $params);

        $stmt = $this->prepare($sql);
        $this->bindParams($stmt, $params);

        $stmt->execute();

        return $stmt; // STREAMING RESULT
    }

    /* =========================
     * RECORD (1 row)
     * ========================= */

    public function record($columns, array $where_params, ?string $from = null)
    {
        $stmt = $this->select($columns, $where_params, [], 0, 1, $from);

        return $stmt->fetch();
    }

    /* =========================
     * COLUMN
     * ========================= */

    public function column(string $column, array $where_params, array $order = [], ?string $from = null)
    {
        $row = $this->record($column, $where_params, $from);

        return $row[0] ?? null;
    }

    /* =========================
     * DELETE
     * ========================= */

    public function delete(array $where_params, ?string $from = null)
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

        parent::logger($sql, $params);

        $stmt = $this->prepare($sql);
        $this->bindParams($stmt, $params);

        return $stmt->execute();
    }

    /* =========================
     * INSERT
     * ========================= */

    public function insert(array $insert_params)
    {
        $columns = '';
        $params = [];

        $values = parent::parse_insert(
            $insert_params,
            $columns,
            $params,
            static::TABLE_COLUMNS,
            static::TABLE_FUNCTIONS
        );

        $sql = parent::_insert($columns, $values, static::TABLE_NAME);

        parent::logger($sql, $params);

        $stmt = $this->prepare($sql);
        $this->bindParams($stmt, $params);

        return $stmt->execute();
    }

    /* =========================
     * UPDATE
     * ========================= */

    public function update(array $update_params, array $where_params)
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

        $set = parent::parse_update(
            $update_params,
            $params,
            static::TABLE_COLUMNS,
            static::TABLE_FUNCTIONS
        );

        $sql = parent::_update($set, $where, static::TABLE_NAME);

        parent::logger($sql, $params);

        $stmt = $this->prepare($sql);
        $this->bindParams($stmt, $params);

        return $stmt->execute();
    }

    /* =========================
     * ARRAY (single column)
     * ========================= */

    public function array(string $column, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null)
    {
        $stmt = $this->select($column, $where_params, $order, $offset, $count, $from);

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row[$column];
        }

        return $result;
    }
}
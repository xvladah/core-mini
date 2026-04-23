<?php

class TDBOMSSQL extends TDBO
{
    /**
     * Zakladni SELECT z databaze
     *
     * @param mixed $columns
     * @param array $where_params
     * @param array $order
     * @param int $offset
     * @param int $count
     * @param ?string $from
     * @return PDOStatement
     *
     * @throws ESQLBase
     * @example
     * SELECT uzi_prijmeni AS prijmeni FROM uzivatele WHERE uzi_pk_id > 10 ORDER BY uzi_prijmeni ASC
     *
     * $result = $query->select(['prijmeni'], ['>uzivatel_id'=>10], ['prijmeni'=>'ASC]);
     */
    public function select(mixed $columns, array $where_params = [], array $order = [], int $offset = 0, int $count = 0, ?string $from = null): PDOStatement
    {
        $params = [];
        $turn = 1;
        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_select_mssql($columns, $where, $order, $offset, $count, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stms = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
        $stms->execute($params);

        return $stms;
    }

    /**
     * @throws ESQLBase
     */
    public function record($columns, array $where_params = [], array $order = [], ?string $from = null): array
    {
        $params = [];
        $turn = 1;
        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_select_mssql($columns, $where, $order, 0, 1, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stms = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
        $stms->execute($params);

        if($stms->rowCount() != 1)
            throw new  EPDOBase('SQL RECORD: Data not found', -300);

        return $stms->fetch();
    }

    /**
     * @throws ESQLBase
     */
    public function column(string $column, array $where_params, array $order = [], ?string $from = null): string
    {
        $params = [];
        $turn = 1;
        $where  = parent::parse_where($where_params, $params, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS, static::TABLE_CONDITIONS, $this->LOGIC, $turn);

        if($from === null)
            $from = static::TABLE_NAME;

        $sql = parent::_column_mssql($column, $where, $order, $from, static::TABLE_COLUMNS, static::TABLE_FUNCTIONS);

        parent::logger($sql, $params);

        $stms = $this->pdo->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL]);
        $stms->execute($params);

        if($stms->rowCount() != 1)
            throw new  EPDOBase('SQL COLUMN: Data not found', -300);

        return $stms->fetchColumn(0);
    }
}




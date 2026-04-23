<?php

declare(strict_types=1);

class TMySQLStatement
{
    private mysqli_stmt $stmt;
    private TMySQL $db;
    private string $sql;

    private array $binds = [];
    private float $start;
    private ?mysqli_result $result = null;

    public function __construct(mysqli_stmt $stmt, TMySQL $db, string $sql)
    {
        $this->stmt = $stmt;
        $this->db   = $db;
        $this->sql  = $sql;
    }

    public function bind(array $params): void
    {
        if (empty($params)) return;

        $types = '';
        $values = [];

        foreach ($params as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }

            $values[] = $value;
        }

        $this->binds = $params;

        $this->stmt->bind_param($types, ...$values);
    }

    public function execute(bool $getResult = true): ?bool
    {
        $this->start = microtime(true);

        $r = $this->stmt->execute();

        if($getResult)
            $res = $this->stmt->get_result();
        else
            $res = null;

        $this->db->logExecution(
            $this->sql,
            $this->start,
            $this->binds
        );

        if (!$r) {
            throw new Exception($this->stmt->error);
        }

        $this->result = ($res === false) ? null : $res;

        return true;
    }

    public function fetch(): ?array
    {
        if (!$this->result) return null;

        return $this->result->fetch_assoc() ?: null;
    }

    public function fetchNum(): ?array
    {
        if (!$this->result) return null;
        return $this->result->fetch_row();
    }

    public function fetchAll(): array
    {
        if (!$this->result) return [];

        return $this->result->fetch_all(MYSQLI_ASSOC);
    }

    public function rowCount(): int
    {
        return $this->stmt->affected_rows;
    }

    public function affectedRows(): int
    {
        return $this->stmt->affected_rows;
    }

    public function close(): void
    {
        $this->stmt->free_result();
        $this->db->unregister($this);
    }

    public function __destruct()
    {
        $this->close();
    }
}
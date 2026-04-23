<?php

declare(strict_types=1);

class TOCI8Statement
{
    private $stmt;
    private TOCI8 $db;
    private array $binds = [];
    private float $start;

    public function __construct($stmt, TOCI8 $db)
    {
        $this->stmt = $stmt;
        $this->db   = $db;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function bind(string $param, &$value): void
    {
        $this->binds[$param] = &$value;
        oci_bind_by_name($this->stmt, $param, $value);
    }

    public function execute(): bool
    {
        $this->start = microtime(true);

        $r = oci_execute($this->stmt, OCI_NO_AUTO_COMMIT);

        $this->db->logExecution(
            $this->getSql(),
            $this->start,
            $this->binds
        );

        if (!$r) {
            $e = oci_error($this->stmt);
            throw new Exception($e['message']);
        }

        return true;
    }

    public function fetch()
    {
        return oci_fetch_array($this->stmt, OCI_ASSOC + OCI_RETURN_NULLS);
    }

    public function fetchAll(int $limit = 1000): array
    {
        $rows = [];

        while (($row = oci_fetch_array($this->stmt, OCI_ASSOC + OCI_RETURN_NULLS)) !== false) {
            $rows[] = $row;

            if (count($rows) >= $limit) {
                break;
            }
        }

        return $rows;
    }

    public function rowCount(): int
    {
        return oci_num_rows($this->stmt);
    }

    public function close(): void
    {
        @oci_free_statement($this->stmt);
        $this->db->unregister($this);
    }

    public function getSql(): string
    {
        return 'OCI statement'; // OCI bohužel nedá SQL zpět
    }

}
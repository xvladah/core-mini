<?php

declare(strict_types=1);

class TOCI8
{
    private $conn;
    private ?string $database = null;
    private array $config = [];

    /* =========================
     * CONNECTION (POOLING)
     * ========================= */

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
        $this->initSession();
    }

    public function __destruct()
    {
        $this->conn = null;
    }

    private function connect(): void
    {
        $tns = "//{$this->config['host']}:{$this->config['port']}/{$this->config['database']}";

        $this->conn = oci_pconnect(
            $this->config['login'],
            $this->config['password'],
            $tns,
            $this->config['charset'] ?? 'AL32UTF8'
        );

        if (!$this->conn) {
            $e = oci_error();
            throw new Exception($e['message']);
        }

        $this->database = $this->config['database'];
    }

    /* =========================
     * SESSION SAFE SETTINGS
     * ========================= */

    private function initSession(): void
    {
        $this->exec("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $this->exec("ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS.FF'");
        $this->exec("ALTER SESSION SET NLS_LANGUAGE='CZECH'");
        $this->exec("ALTER SESSION SET NLS_TERRITORY='CZECH REPUBLIC'");
        $this->exec("ALTER SESSION SET TIME_ZONE='Europe/Prague'");

        //$this->exec("ALTER SESSION SET NLS_CHARACTERSET='AL32UTF8'");
    }

    /* =========================
     * QUERY
     * ========================= */

    public function query(string $sql): TOCI8Statement
    {
        $stmt = oci_parse($this->conn, $sql);

        if (!$stmt) {
            throw new Exception(oci_error($this->conn)['message']);
        }

        return new TOCI8Statement($stmt);
    }

    public function exec(string $sql): int
    {
        $stmt = oci_parse($this->conn, $sql);

        if (!$stmt) {
            throw new Exception(oci_error($this->conn)['message']);
        }

        oci_execute($stmt);
        return oci_num_rows($stmt);
    }

    /* =========================
     * TRANSACTIONS
     * ========================= */

    public function begin(): void
    {
        oci_execute(oci_parse($this->conn, "BEGIN"));
    }

    public function commit(): void
    {
        oci_commit($this->conn);
    }

    public function rollback(): void
    {
        oci_rollback($this->conn);
    }

    /* =========================
     * HELPERS
     * ========================= */

    public function getDatabase(): ?string
    {
        return $this->database;
    }

    public function select_db(string $schema): int
    {
        $this->database = $schema;
        return $this->exec("ALTER SESSION SET CURRENT_SCHEMA = $schema");
    }
}
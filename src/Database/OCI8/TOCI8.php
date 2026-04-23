<?php

declare(strict_types=1);

class TOCI8
{
    private $conn;
    private ?string $database = null;
    private array $config = [];

    /** @var array<string, resource> */
    private array $stmtCache = [];

    /** @var array<int, TOCI8Statement> */
    private array $activeStatements = [];

    private bool $debug = false;
    private array $log = [];

    private bool $inTransaction = false;
    private int $txDepth = 0;

    /* =========================
     * CONSTRUCTOR
     * ========================= */

    public function __construct(array $config, bool $debug = false)
    {
        $this->config = $config;
        $this->debug  = $debug;

        $this->connect();
        $this->initSession();
    }

    public function __destruct()
    {
        $this->cleanup();
        $this->conn = null;
    }

    /* =========================
     * CONNECTION (POOLING)
     * ========================= */

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
     * SESSION SETTINGS
     * ========================= */

    private function initSession(): void
    {
        $this->exec("ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD HH24:MI:SS'");
        $this->exec("ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD HH24:MI:SS.FF'");
        $this->exec("ALTER SESSION SET NLS_LANGUAGE='CZECH'");
        $this->exec("ALTER SESSION SET NLS_TERRITORY='CZECH REPUBLIC'");
        $this->exec("ALTER SESSION SET TIME_ZONE='Europe/Prague'");
    }

    /* =========================
     * QUERY (WITH CACHE)
     * ========================= */

    public function query(string $sql): TOCI8Statement
    {
        $start = microtime(true);

        if (!isset($this->stmtCache[$sql])) {
            $stmt = oci_parse($this->conn, $sql);

            if (!$stmt) {
                throw new Exception(oci_error($this->conn)['message']);
            }

            $this->stmtCache[$sql] = $stmt;
        } else {
            $stmt = $this->stmtCache[$sql];
        }

        $wrapper = new TOCI8Statement($stmt, $this);

        $this->activeStatements[spl_object_id($wrapper)] = $wrapper;

        if ($this->debug) {
            $this->profileLog[] = [
                'sql' => $sql,
                'time_start' => $start
            ];
        }

        return $wrapper;
    }

    public function exec(string $sql): int
    {
        $stmt = $this->query($sql);
        $stmt->execute();

        return $stmt->rowCount();
    }

    /* =========================
     * CLEANUP
     * ========================= */

    public function cleanup(): void
    {
        foreach ($this->activeStatements as $stmt) {
            $stmt->close();
        }

        $this->activeStatements = [];
    }

    public function unregister(TOCI8Statement $stmt): void
    {
        unset($this->activeStatements[spl_object_id($stmt)]);
    }

    /* =========================
     * TRANSACTIONS
     * ========================= */

    public function beginTransaction(): void
    {
        if ($this->txDepth === 0) {
            $this->inTransaction = true;
        }

        $this->txDepth++;
    }

    public function commitTransaction(): bool
    {
        $this->txDepth = max(0, $this->txDepth - 1);

        if ($this->txDepth === 0) {
            $this->inTransaction = false;
            return oci_commit($this->conn);
        }

        return true;
    }

    public function rollbackTransaction(): bool
    {
        $this->txDepth = 0;
        $this->inTransaction = false;

        return oci_rollback($this->conn);
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }


    /* =========================
     * DEBUG / PROFILER
     * ========================= */

    public function logExecution(string $sql, float $start, array $binds): void
    {
        if (!$this->debug) return;

        $this->log[] = [
            'sql'   => $sql,
            'time'  => round((microtime(true) - $start) * 1000, 2) . ' ms',
            'binds' => $binds
        ];
    }

    public function getLog(): array
    {
        return $this->log;
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
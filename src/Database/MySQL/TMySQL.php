<?php

declare(strict_types=1);

class TMySQL
{
    private mysqli $conn;
    private array $config;

    /** @var array<string, mysqli_stmt> */
    private array $stmtCache = [];

    /** @var array<int, TMySQLStatement> */
    private array $activeStatements = [];

    private bool $debug = false;
    private array $log = [];

    private bool $inTransaction = false;

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
        $this->conn->close();
    }

    public function connect(): void
    {
        $host = $this->config['host'];

        if (!empty($this->config['persistent'])) {
            $host = 'p:' . $host;
        }

        $this->conn = new mysqli(
            $host,
            $this->config['login'],
            $this->config['password'],
            $this->config['database'],
            $this->config['port'] ?? 3306
        );

        if ($this->conn->connect_error) {
            throw new Exception($this->conn->connect_error);
        }

        $this->conn->set_charset($this->config['charset'] ?? 'utf8mb4');
    }

    private function initSession(): void
    {
        $this->conn->set_charset($this->config['charset'] ?? 'utf8mb4');

        $this->exec("SET time_zone = SYSTEM"); // SAFE DEFAULT
        //$this->exec("SET time_zone = 'Europe/Prague'");

        if (!empty($this->config['timeout'])) {
            $this->setTimeOut((int)$this->config['timeout']);
        }
    }

    public function setTimeOut(int $seconds): void
    {
        $seconds = (int)$seconds;

        $this->exec("SET SESSION wait_timeout = $seconds");
        $this->exec("SET SESSION interactive_timeout = $seconds");
    }

    public function setQueryTimeout(int $ms): void
    {
        $this->exec("SET SESSION max_execution_time = $ms");
    }

    /* =========================
     * QUERY (CACHE)
     * ========================= */

    public function query(string $sql): TMySQLStatement
    {
        $start = microtime(true);

        if (!isset($this->stmtCache[$sql])) {
            $stmt = $this->conn->prepare($sql);

            if (!$stmt) {
                throw new Exception($this->conn->error);
            }

            $this->stmtCache[$sql] = $stmt;
        } else {
            $stmt = $this->stmtCache[$sql];
        }

        $wrapper = new TMySQLStatement($stmt, $this, $sql);

        $this->activeStatements[spl_object_id($wrapper)] = $wrapper;

        if ($this->debug) {
            $this->log[] = [
                'sql' => $sql,
                'time_start' => $start
            ];
        }

        return $wrapper;
    }

    public function exec(string $sql): int
    {
        if (!$this->conn->query($sql)) {
            throw new Exception($this->conn->error);
        }

        return $this->conn->affected_rows;
    }

    public function prepare(string $sql): TMySQLStatement
    {
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($this->conn->error);
        }

        return new TMySQLStatement($stmt, $this, $sql);
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

    public function unregister(TMySQLStatement $stmt): void
    {
        unset($this->activeStatements[spl_object_id($stmt)]);
    }

    /* =========================
     * TRANSACTIONS
     * ========================= */

    public function beginTransaction(): void
    {
        $this->conn->begin_transaction();
        $this->inTransaction = true;
    }

    public function commitTransaction(): bool
    {
        $result = $this->conn->commit();
        $this->inTransaction = false;
        return $result;
    }

    public function rollbackTransaction(): bool
    {
        $result = $this->conn->rollback();
        $this->inTransaction = false;
        return $result;
    }

    public function inTransaction(): bool
    {
        return $this->inTransaction;
    }

    /* =========================
     * DEBUG
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

    public function getLastInsertId(): int
    {
        return $this->conn->insert_id;
    }

    /* =========================
    * EXPORT
    * ========================= */

    public function exportDatabase(array $options = []): string
    {
        $sqlOut = "";

        $addDrop   = $options['drop_table'] ?? false;
        $lockTables = $options['lock_tables'] ?? false;
        $disableKeys = $options['disable_keys'] ?? false;

        $tables = $this->fetchAll("SHOW TABLES");

        $tableList = [];

        foreach ($tables as $row) {
            $tableList[] = array_values($row)[0];
        }

        // =========================
        // LOCK TABLES (global)
        // =========================
        if ($lockTables && count($tableList) > 0) {
            $sqlOut .= "LOCK TABLES ";

            $locks = [];

            foreach ($tableList as $t) {
                $locks[] = "`$t` READ";
            }

            $sqlOut .= implode(", ", $locks) . ";\n\n";
        }

        foreach ($tableList as $table) {

            $sqlOut .= "\n\n-- =========================\n";
            $sqlOut .= "-- TABLE: $table\n";
            $sqlOut .= "-- =========================\n\n";

            // =========================
            // DROP TABLE
            // =========================
            if ($addDrop) {
                $sqlOut .= "DROP TABLE IF EXISTS `$table`;\n\n";
            }

            // =========================
            // CREATE TABLE
            // =========================
            $create = $this->fetchRow("SHOW CREATE TABLE `$table`");

            if (!empty($create['Create Table'])) {
                $sqlOut .= $create['Create Table'] . ";\n\n";
            }

            // =========================
            // DISABLE KEYS
            // =========================
            if ($disableKeys) {
                $sqlOut .= "ALTER TABLE `$table` DISABLE KEYS;\n";
            }

            // =========================
            // DATA
            // =========================
            $rows = $this->fetchAll("SELECT * FROM `$table`");

            if (!empty($rows)) {

                $columns = array_keys($rows[0]);

                $sqlOut .= "\n-- DATA\n";

                $sqlOut .= "INSERT INTO `$table` (`" . implode('`,`', $columns) . "`) VALUES\n";

                $valuesAll = [];

                foreach ($rows as $r) {

                    $vals = [];

                    foreach ($r as $v) {
                        $vals[] = $this->exportValue($v);
                    }

                    $valuesAll[] = "(" . implode(",", $vals) . ")";
                }

                $sqlOut .= implode(",\n", $valuesAll) . ";\n";
            }

            // =========================
            // ENABLE KEYS
            // =========================
            if ($disableKeys) {
                $sqlOut .= "\nALTER TABLE `$table` ENABLE KEYS;\n";
            }
        }

        // =========================
        // UNLOCK TABLES
        // =========================
        if ($lockTables && count($tableList) > 0) {
            $sqlOut .= "\nUNLOCK TABLES;\n";
        }

        return $sqlOut;
    }

    private function exportValue($v): string
    {
        // NULL SAFE
        if ($v === null) {
            return "NULL";
        }

        // BOOLEAN
        if (is_bool($v)) {
            return $v ? "1" : "0";
        }

        // NUMERIC
        if (is_int($v) || is_float($v)) {
            return (string)$v;
        }

        if (!is_string($v)) {
            return "''";
        }

        // DATETIME detection (robust)
        if ($this->isDateTime($v)) {
            return "'" . $this->escape($v) . "'";
        }

        return "'" . $this->escape($v) . "'";
    }

    private function isDateTime(string $value): bool
    {
        return preg_match(
                '/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}:\d{2}(\.\d+)?)?$/',
                $value
            ) === 1;
    }

    private function fetchAll(string $sql): array
    {
        $res = $this->conn->query($sql);

        if (!$res) {
            throw new Exception($this->conn->error);
        }

        return $res->fetch_all(MYSQLI_ASSOC);
    }

    private function fetchRow(string $sql): ?array
    {
        $res = $this->conn->query($sql);

        if (!$res) {
            throw new Exception($this->conn->error);
        }

        return $res->fetch_assoc() ?: null;
    }

    private function escape(string $value): string
    {
        // pokud máš mysqli
        if (method_exists($this, 'conn') && $this->conn instanceof mysqli) {
            return $this->conn->real_escape_string($value);
        }

        // fallback
        return addslashes($value);
    }

    /* ===================
     * IMPORT
       ===================*/

    public function importDatabase(string $sql, array $options = []): void
    {
        $this->exec("SET FOREIGN_KEY_CHECKS=0");

        $this->beginTransactionSafe();

        try {
            $statements = $this->tokenizeSQL($sql);

            foreach ($statements as $statement) {
                $this->executeImportStatement($statement, $options);
            }

            $this->commitTransactionSafe();
            $this->exec("SET FOREIGN_KEY_CHECKS=1");

        } catch (Throwable $e) {

            $this->rollbackTransactionSafe();
            $this->exec("SET FOREIGN_KEY_CHECKS=1");

            throw $e;
        }
    }

    private function tokenizeSQL(string $sql): array
    {
        $statements = [];
        $buffer = '';

        $len = strlen($sql);

        $inSingle = false;
        $inDouble = false;
        $escape = false;

        for ($i = 0; $i < $len; $i++) {

            $ch = $sql[$i];

            // handle escape inside string
            if ($escape) {
                $buffer .= $ch;
                $escape = false;
                continue;
            }

            if ($ch === '\\') {
                $buffer .= $ch;
                $escape = true;
                continue;
            }

            // toggle string states
            if ($ch === "'" && !$inDouble) {
                $inSingle = !$inSingle;
            } elseif ($ch === '"' && !$inSingle) {
                $inDouble = !$inDouble;
            }

            // statement end ONLY if not inside string
            if ($ch === ';' && !$inSingle && !$inDouble) {

                $stmt = trim($buffer);
                if ($stmt !== '') {
                    $statements[] = $stmt;
                }

                $buffer = '';
                continue;
            }

            $buffer .= $ch;
        }

        // last statement
        $last = trim($buffer);
        if ($last !== '') {
            $statements[] = $last;
        }

        return $statements;
    }

    private function beginTransactionSafe(): void
    {
        // MySQL / OCI safe generic
        $this->exec("START TRANSACTION");
    }

    private function commitTransactionSafe(): void
    {
        $this->exec("COMMIT");
    }

    private function rollbackTransactionSafe(): void
    {
        $this->exec("ROLLBACK");
    }

    private function executeImportStatement(string $sql, array $options = []): void
    {
        $sqlUpper = strtoupper(ltrim($sql));

        // control statements
        if (
            str_starts_with($sqlUpper, 'LOCK TABLES') ||
            str_starts_with($sqlUpper, 'UNLOCK TABLES') ||
            str_starts_with($sqlUpper, 'ALTER TABLE') ||
            str_starts_with($sqlUpper, 'SET FOREIGN_KEY_CHECKS')
        ) {
            $this->exec($sql);
            return;
        }

        if (str_starts_with($sqlUpper, 'DROP TABLE')) {
            if (!($options['allow_drop'] ?? true)) {
                return;
            }
        }

        if (
            str_starts_with($sqlUpper, 'CREATE TABLE') ||
            str_starts_with($sqlUpper, 'INSERT INTO') ||
            str_starts_with($sqlUpper, 'DROP TABLE')
        ) {
            $this->exec($sql);
            return;
        }

        // fallback
        $this->exec($sql);
    }
}
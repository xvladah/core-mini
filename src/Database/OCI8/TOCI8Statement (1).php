<?php

declare(strict_types=1);

class TOCI8Statement
{
    private $stmt;
    private bool $closed = false;
    private array $bound = [];

    public function __construct($stmt)
    {
        $this->stmt = $stmt;
    }

    public function __destruct()
    {
        $this->close();
    }

    /* =========================
     * BIND VARIABLES
     * ========================= */

    public function bind(string $param, $value, int $type = SQLT_CHR): self
    {
        oci_bind_by_name($this->stmt, $param, $value, -1, $type);
        $this->bound[$param] = $value;

        return $this;
    }

    /* =========================
     * EXECUTE
     * ========================= */

    public function execute(): bool
    {
        $ok = oci_execute($this->stmt, OCI_COMMIT_ON_SUCCESS);

        if (!$ok) {
            $e = oci_error($this->stmt);
            throw new Exception($e['message']);
        }

        return true;
    }

    /* =========================
     * FETCH METHODS
     * ========================= */

    public function fetch(): ?array
    {
        if (!$this->stmt) {
            return null;
        }

        $row = oci_fetch_array($this->stmt,OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS);

        if ($row === false) {
            $this->close();
            return null;
        }

        return $row;
    }

    public function fetchAll(): array
    {
        $out = [];

        while ($row = oci_fetch_array($this->stmt,OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS)) {
            $out[] = $row;
        }

        $this->close();

        return $out;
    }

    public function fetchAllBulk(int $chunkSize = 1000): array
    {
        $result = [];

        // OCI prefetch (důležité pro výkon)
        if (function_exists('oci_set_prefetch')) {
            oci_set_prefetch($this->stmt, $chunkSize);
        }

        while (($row = oci_fetch_array($this->stmt,OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS))) {
            $result[] = $row;

            // lehká ochrana proti memory spike
            if (count($result) % 5000 === 0) {
                gc_collect_cycles();
            }
        }

        $this->close();

        return $result;
    }

    public function fetchLazyBulk(int $chunkSize = 1000): Generator
    {
        if (function_exists('oci_set_prefetch')) {
            oci_set_prefetch($this->stmt, $chunkSize);
        }

        while ($row = oci_fetch_array($this->stmt,OCI_ASSOC + OCI_RETURN_NULLS + OCI_RETURN_LOBS)) {
            yield $row;
        }

        $this->close();
    }

    public function rowCount(): int
    {
        return oci_num_rows($this->stmt);
    }

    public function close(): void
    {
        if (!$this->closed && $this->stmt) {
            oci_free_statement($this->stmt);
            $this->stmt = null;
            $this->closed = true;
        }
    }
}
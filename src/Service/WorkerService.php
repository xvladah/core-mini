<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

namespace Core\Service;

use PDO;
use PDOException;

class WorkerService implements WorkerInterface {
    private array $config;
    private ?PDO $pdo = null;

    public function __construct(array $serviceConfig) {
        $this->config = $serviceConfig;
    }

    // připojení k DB, volá se před smyčkou a při reconnect
    public function init(): void {
        $dbConf = $this->config['db'] ?? [];
        if (empty($dbConf['dsn'])) {
            throw new \RuntimeException("DB DSN not configured for service {$this->config['name']}");
        }

        try {
            $this->pdo = new PDO(
                $dbConf['dsn'],
                $dbConf['user'] ?? null,
                $dbConf['password'] ?? null,
                $dbConf['options'] ?? []
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \RuntimeException("Cannot connect to DB: " . $e->getMessage());
        }
    }

    public function doWork(): void {
        if (!$this->pdo) {
            $this->init(); // připojení, pokud ještě není
        }

        try {
            $stmt = $this->pdo->query("SELECT id, value FROM my_table WHERE processed = 0");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) return; // nic k přepočtu

            foreach ($rows as $row) {
                $row['value'] = $this->recalculate($row['value']);
            }

            $updateStmt = $this->pdo->prepare(
                "UPDATE my_table SET value = :value, processed = 1 WHERE id = :id"
            );

            foreach ($rows as $row) {
                $updateStmt->execute([
                    ':value' => $row['value'],
                    ':id'    => $row['id']
                ]);
            }

        } catch (\PDOException $e) {
            // pokud spojení spadlo, nuluj PDO a příště se reconnectne
            $this->pdo = null;
            throw $e;
        }

        $sleep = (float)($this->config['sleep_time'] ?? 1.0);
        usleep((int)($sleep * 1000000));
    }

    private function recalculate($value): float
    {
        return $value * 1.1; // příklad přepočtu
    }
}
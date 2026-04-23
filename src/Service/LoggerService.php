<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

namespace Core\Service;

class LoggerService implements LoggerInterface {
    private string $file;
    private float $maxFileSizeMb;
    private int $maxFiles;

    public function __construct(string $file, float $maxFileSizeMb = 10, int $maxFiles = 5) {
        $this->file = $file;
        $this->maxFileSizeMb = $maxFileSizeMb;
        $this->maxFiles = $maxFiles;
    }

    public function log(string $message): void {
        $this->rotateIfNeeded();
        $date = date('Y-m-d H:i:s');
        $line = "[$date] $message\n";

        // zápis do souboru
        file_put_contents($this->file, $line, FILE_APPEND);

        // zápis do stdout pro systemd
        fwrite(STDOUT, $line);
    }

    public function getLogStorage(): string {
        return $this->file;
    }

    private function rotateIfNeeded(): void {
        if (!file_exists($this->file)) return;

        $sizeMb = filesize($this->file) / 1048576;
        if ($sizeMb < $this->maxFileSizeMb) return;

        $oldest = $this->file . '.' . $this->maxFiles;
        if (file_exists($oldest)) unlink($oldest);

        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $old = $this->file . '.' . $i;
            if (file_exists($old)) {
                rename($old, $this->file . '.' . ($i + 1));
            }
        }

        rename($this->file, $this->file . '.1');
    }
}
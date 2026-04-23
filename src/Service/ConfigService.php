<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

namespace Core\Service;

class ConfigService {
    public array $data;

    public function __construct(string $file) {
        if (!file_exists($file)) {
            throw new \RuntimeException("Config file not found: $file");
        }
        $this->data = require $file;
    }

    // vrátí obecnou hodnotu podle cesty, fallback
    public function get(string $path, $default = null) {
        $parts = explode('.', $path);
        $value = $this->data;

        foreach ($parts as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }

        return $value;
    }

    // vrátí konfiguraci konkrétní služby podle jména
    public function getServiceConfig(string $serviceName): array {
        return $this->data['services'][$serviceName] ?? [];
    }

    public function all(): array {
        return $this->data;
    }
}
<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

namespace Core\Service;

interface LoggerInterface {
    public function log(string $message): void;

    public function getLogStorage(): string;
}
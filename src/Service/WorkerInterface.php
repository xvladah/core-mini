<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

namespace Core\Service;

interface WorkerInterface
{
    /**
     * Metoda, která provádí jednu jednotku práce (cyklus)
     */
    public function doWork(): void;

    /**
     * Volitelná metoda pro inicializaci (např. připojení k DB)
     */
    public function init(): void;
}
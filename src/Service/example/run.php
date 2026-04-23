<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */


require_once __DIR__ . '/../../../../vendor/autoload.php';

use Core\Service\ConfigService;
use Core\Service\LoggerService;
use Core\Service\WorkerService;
use Core\Service\MainService;

// načtení konfigurace
$config = new ConfigService(__DIR__ . '/config.php');

// jméno služby z environment proměnné
$serviceName = getenv('SERVICE_NAME') ?: 'serviceA';
$svcConf = $config->getServiceConfig($serviceName);

// přidej watchdog do configu služby (pokud není)
if (!isset($svcConf['watchdog'])) {
    $svcConf['watchdog'] = $config->get('watchdog', []);
}

// přidej název služby
$svcConf['name'] = $serviceName;

// vytvoření loggeru
$logger = new LoggerService($svcConf['log_file'], 10, 5);

// worker s konfigurací konkrétní služby
$worker = new WorkerService($svcConf);

// spuštění hlavní smyčky služby
$service = new MainService($logger, $worker, $svcConf);
$service->run();


<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

/*
    {
        "status": "running",
        "uptime_sec": 312.54,
        "cycles": 210,
        "memory_mb": 1.42,
        "peak_mb": 2.13,
        "cpu_user_sec": 0.456,
        "cpu_sys_sec": 0.032,
        "log_size_kb": 12.5,
        "time": "2025-09-04 21:07:11"
    }
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Core\Service\ConfigService;

// konfigurace pro port podle služby
$config = new ConfigService(__DIR__ . '/../config.php');
$serviceName = getenv('SERVICE_NAME') ?: 'serviceA';
$svcConf = $config->getServiceConfig($serviceName);

$host = $svcConf['listen_host'] ?? '127.0.0.1';
$port = $svcConf['listen_port'] ?? 9000;

$fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, 5);
if (!$fp) die("Connection failed: $errstr ($errno)\n");

fwrite($fp, "STATUS\n");
while (!feof($fp)) {
    echo fgets($fp);
}
fclose($fp);


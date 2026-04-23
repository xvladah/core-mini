<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
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
if (!$fp)
    die("Connection failed: $errstr ($errno)\n");

fwrite($fp, "STOP\n");
echo fgets($fp);
fclose($fp);



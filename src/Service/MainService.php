<?php
/*
 * Copyright (c)  TEDOM a.s.
 * @author Vladimír Horký
 */

namespace Core\Service;

class MainService {
    private LoggerInterface $logger;
    private WorkerInterface $worker;
    private array $serviceConfig;
    private bool $running = true;
    private $socket;

    private int $cycleCount = 0;
    private float $startTime;

    public function __construct(LoggerInterface $logger, WorkerInterface $worker, array $serviceConfig) {
        $this->logger = $logger;
        $this->worker = $worker;
        $this->serviceConfig = $serviceConfig;
        $this->startTime = microtime(true);
    }

    public function run(): void {
        $this->logger->log('Service starting: ' . ($this->serviceConfig['name'] ?? 'unknown'));

        $host = $this->serviceConfig['listen_host'] ?? '127.0.0.1';
        $port = $this->serviceConfig['listen_port'] ?? 9000;
        $address = 'tcp://'.$host.':'.$port;

        $this->socket = stream_socket_server($address, $errno, $errstr);
        if (!$this->socket) {
            $this->logger->log("Error creating socket: $errstr ($errno)");
            return;
        }

        $this->logger->log("Listening on $address");

        while ($this->running) {
            $this->worker->doWork();
            $this->cycleCount++;
            $this->checkWatchdog();

            $read = [$this->socket];
            $write = $except = [];
            if (stream_select($read, $write, $except, 0, 100000)) {
                $client = stream_socket_accept($this->socket, 0);
                if ($client) {
                    $command = trim(fgets($client));
                    $this->handleCommand($command, $client);
                    fclose($client);
                }
            }
        }

        fclose($this->socket);
        $this->logger->log("Service stopped: " . ($this->serviceConfig['name'] ?? 'unknown'));
    }

    private function handleCommand(string $command, $client): void {
        $this->logger->log("Received command: $command");

        switch (strtoupper($command)) {
            case 'STOP':
                    fwrite($client, "Stopping service...\n");
                    $this->running = false;
                break;

            case 'STATUS':
                    $uptime     = microtime(true) - $this->startTime;
                    $memoryMb   = round(memory_get_usage(true) / 1048576, 2);
                    $peakMb     = round(memory_get_peak_usage(true) / 1048576, 2);
                    $rusage     = getrusage();
                    $cpuUser    = ($rusage["ru_utime.tv_sec"] + $rusage["ru_utime.tv_usec"] / 1e6);
                    $cpuSys     = ($rusage["ru_stime.tv_sec"] + $rusage["ru_stime.tv_usec"] / 1e6);
                    $logFile    = $this->logger->getLogStorage();
                    $logSize    = file_exists($logFile) ? filesize($logFile) : 0;

                    $status = [
                        "service"      => $this->serviceConfig['name'] ?? 'unknown',
                        "status"       => $this->running ? "running" : "stopped",
                        "uptime_sec"   => round($uptime, 2),
                        "cycles"       => $this->cycleCount,
                        "memory_mb"    => $memoryMb,
                        "peak_mb"      => $peakMb,
                        "cpu_user_sec" => round($cpuUser, 3),
                        "cpu_sys_sec"  => round($cpuSys, 3),
                        "log_size_kb"  => round($logSize / 1024, 1),
                        "time"         => date("Y-m-d H:i:s")
                    ];

                    fwrite($client, json_encode($status, JSON_PRETTY_PRINT) . "\n");
                break;

            default:
                    fwrite($client, "Unknown command: $command\n");
        }
    }

    private function checkWatchdog(): void {
        $limits = $this->serviceConfig['watchdog'] ?? [];
        $memoryMb = memory_get_usage(true) / 1048576;
        $uptime = microtime(true) - $this->startTime;

        if (!empty($limits['max_memory_mb']) && $memoryMb > $limits['max_memory_mb']) {
            $this->logger->log("Watchdog: memory limit exceeded ({$memoryMb} MB). Exiting...");
            exit(1);
        }

        if (!empty($limits['max_uptime_sec']) && $uptime > $limits['max_uptime_sec']) {
            $this->logger->log("Watchdog: uptime limit exceeded ({$uptime} sec). Exiting...");
            exit(1);
        }

        if (!empty($limits['max_cycles']) && $this->cycleCount > $limits['max_cycles']) {
            $this->logger->log("Watchdog: cycle limit exceeded ({$this->cycleCount}). Exiting...");
            exit(1);
        }
    }
}
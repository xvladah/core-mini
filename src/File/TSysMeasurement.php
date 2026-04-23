<?php

declare(strict_types=1);

class TSysMeasurement
{
    protected float $startTime = 0;
    protected float $endTime = 0;
    protected int $startMemoryUsage = 0;
    protected int $endMemoryUsage = 0;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->startMemoryUsage = memory_get_peak_usage();
    }

    public function getArrayMeasurement() :array
    {
        $this->endTime = microtime(true);
        $this->endMemoryUsage = memory_get_peak_usage();

        return [
            'time'   => round($this->endTime - $this->startTime, 4),
            'memory' => ($this->endMemoryUsage - $this->startMemoryUsage)
        ];
    }

    public function getTimeDuration() :?float
    {
        if($this->startTime != '')
        {
            $this->endTime = microtime(true);
            return round($this->endTime - $this->startTime, 4);
        } else
            return null;
    }

    public function getMemorySize() :?int
    {
        if($this->startMemoryUsage != '')
        {
            $this->endMemoryUsage = memory_get_peak_usage();
            return $this->endMemoryUsage - $this->startMemoryUsage;
        } else
            return null;
    }
}
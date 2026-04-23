<?php

declare(strict_types=1);

abstract class TTimeIntervalRepository extends TBaseRepository
{
    public array $resampleData = [];

    public int $startTimeStamp = 0;
    public int $konecTimeStamp = 0;

    public bool $resampleReadOnly = false;

    public function timeRound(int $time, int $roundTo): int
    {
        return (int)floor($time / $roundTo) * $roundTo;
    }

    abstract function dateTimeResample(int $roundTo, ?int $dateTimeFrom = null, ?int $dateTimeTo = null): void;

    public function init(int $id, ?int $second = null) :void
    {
        if(!key_exists($id, $this->resampleData))
            $this->resampleData[$id] = [];

        if($second != '')
            if(!key_exists($second, $this->resampleData[$id]))
                $this->resampleData[$id][$second] = [];
    }

    public function exists(int $id, int $second) :bool
    {
        $result = false;

        if(key_exists($id, $this->resampleData))
            $result = key_exists($second, $this->resampleData[$id]);

        return $result;
    }

    public function remove(int $id, int $second, ?int $typId = null) :void
    {
        if(key_exists($id, $this->resampleData))
        {
            if(key_exists($second, $this->resampleData[$id]))
            {
                if($typId === null)
                    unset($this->resampleData[$id][$second]);
                else
                    if($this->resampleData[$id][$second]['typ_id'] == $typId)
                        unset($this->resampleData[$id][$second]);
            }

            if(count($this->resampleData[$id]) === 0)
                unset($this->resampleData[$id]);
        }
    }

    public function initStartKonec(?string $datum = null): void
    {
        if($datum != '')
            TDateTime::initStartKonecTimeStamp($datum, TConfig::TIMEZONE_LOCAL, $this->startTimeStamp, $this->konecTimeStamp);
        else
            TDateTime::initStartKonecTimeStamp($this->params['datum'], TConfig::TIMEZONE_LOCAL, $this->startTimeStamp, $this->konecTimeStamp);
    }

    public function clearAll(?int $startTimeStamp, ?int $konecTimeStamp, ?array $idArray = null): int
    {
        $result = 0;

        foreach($this->resampleData as $id => $seconds)
        {
            if($idArray === null || in_array($id, $idArray))
            {
                foreach($seconds as $second => $records)
                {
                    if($startTimeStamp === null || $startTimeStamp <= $second)
                    {
                        if($konecTimeStamp === null || $konecTimeStamp >= $second)
                        {
                            unset($this->resampleData[$id][$second]);
                            $result++;
                        }
                    }
                }
            }
        }

        return $result;
    }
}
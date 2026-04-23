<?php

declare(strict_types=1);

class TOCI8UTF8
{
    public static function encode($value)
    {
        return is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'UTF-8') : $value;
    }

    public static function decode($value)
    {
        return $value;
    }
}
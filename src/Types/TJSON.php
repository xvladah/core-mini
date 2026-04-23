<?php

class TJSON
{
    public static function jsonTrimToFit(?array $data, int $maxLength = 65400): string
    {
        if($data != '')
        {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE);

            if (strlen($json) > $maxLength)
                $json = substr($json, 0, $maxLength - 3) . '...';

            return $json;
        } else
            return '';
    }

    public static function recoverPartialJson(?string $json): ?string
    {
        if(str_ends_with($json, '...') && strlen($json) > 65390)
            $json = '{"text":"Data jsou příliš velká pro zobrazení"}';

        return $json;
    }

    public static function is_json(?string $json): bool
    {
        return is_string($json) && is_array(json_decode($json, true)) && (json_last_error() == JSON_ERROR_NONE);
    }
}
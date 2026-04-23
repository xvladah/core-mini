<?php

/**
* Základní metody
*
* @name TMethods
* @version 1.0
* @author vladimir.horky
* @copyright Vladimír Horký
*/

class TMethods
{
public static function getPersonalName(array $osoba, string $filter = 'name', string $prefix = '') :string
{
    $result = $osoba[$prefix.'prijmeni'];

    if($result != '')
    {
        switch($filter)
        {
            case 'short' :
                if($osoba[$prefix.'jmeno'] != '')
                    $result .= ' ' . $osoba[$prefix.'jmeno'][0].'.';

                if($osoba[$prefix.'dodatek'] != '')
                    $result .= ' '.$osoba[$prefix.'dodatek'];

                break;

            case 'full'  : $result = $osoba[$prefix.'jmeno'] . ' ' . $result;

                if($osoba[$prefix.'dodatek'] != '')
                    $result .= ' '.$osoba[$prefix.'dodatek'];

                if($osoba[$prefix.'titul_pred'] != '')
                    $result = $osoba[$prefix.'titul_pred'] .' '.$result;

                if($osoba[$prefix.'titul_za'] != '')
                    $result .= ', '.$osoba[$prefix.'titul_za'];

                break;

            case 'name'  :
            default 	 :	$result .= ' ' . $osoba[$prefix.'jmeno'];

                if($osoba[$prefix.'dodatek'] != '')
                    $result .= ' '.$osoba[$prefix.'dodatek'];

                break;
        }
    }

    return $result;
}

public static function getAddress(string $adresa) :string
{
    $result = '';

    if($adresa['ulice'] != '')
        $result = $adresa['ulice'];

    if($adresa['mesto'] != '')
    {
        if($result != '')
            $result .= ', ';

        $result .= $adresa['mesto'];

        if($adresa['psc'] != '')
        {
            if($result != '')
                $result .= ' ';

            $result .= $adresa['psc'];
        }
    }

    return $result;
}

public static function strtohtml(?string $str) :string
{
    if($str != '')
        return addslashes($str);
    else
        return '';
}

 public static function attrtohtml(?string $str) :string
 {
     if($str != '')
         return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5);
     else
         return '';
 }

    public static function pencrypt(string $text) :string
    {
        $crypt = new TCrypt();
        return $crypt->encrypt($text);
    }

    public static function pdecrypt(string $text) :string
    {
        $crypt = new TCrypt();
        return $crypt->decrypt($text);
    }

    public static function debug(array $array, bool $die = false): void
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';

        if($die)
            die();
    }

    public static function logErrors(string $text): void
    {
        $file = __DIR__ . '/../../logs/errors.log';
        $current  = "[" . Date("d.m.Y H:i:s") . "] " . $text . "\n";
        file_put_contents($file, $current,  FILE_APPEND | LOCK_EX);
    }

    public static function logErrorsClear(): void
    {
        $file = __DIR__ . '/../../logs/errors.log';
        file_put_contents($file, '', LOCK_EX);
    }
}
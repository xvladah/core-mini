<?php

/**
 * Trida pro praci s čísly
 *
 * @name TNumbers
 * @version 2.0
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 *
 * version 2.0
 * Changed functions, added rtrim
 *
 * version 1.6
 * Changed valuestr
 *
 * version 1.5
 * Added $number != '' conditions
 *
 * version 1.4
 * Added new function valuestr
 *
 * version 1.3
 * Added new function value
 * *
 * version 1.2
 * Added new function strtonumber
 * Added new function strtoint
 * Added new function strtofloat
 * *
 * version 1.1
 * Added new function round_up
 */

//declare(strict_types=1);

class TNumbers {

    const int SMALLINT_SIGNED_MIN = -32768;
    const int SMALLINT_SIGNED_MAX = 32767;
    const int SMALLINT_UNSIGNED_MIN = 0;
    const int SMALLINT_UNSIGNED_MAX = 65535;

    const int TINYINT_SIGNED_MIN = -128;
    const int TINYINT_SIGNED_MAX = 127;
    const int TINYINT_UNSIGNED_MIN = 0;
    const int TINYINT_UNSIGNED_MAX = 255;

	/**
	 * Funkce vrací formátovaný řetězec ze zadaného reálného čísla ve formátu '# ###,##' s daným počtem desetinných míst
	 *
	 * @param float $number
	 * @param number $dec
	 * @return string
	 */
	public static function format(string|int|float|null $number, int $dec = 0, bool $trim = false): string
    {
		if($number != '')
        {
            $result = number_format(floatval($number), $dec, ',', ' ');

            if($trim && $result != '')
            {
                $result = rtrim($result, '0 ');
                $result = rtrim($result, ',.');
            }
        } else
			$result = '';

        return $result;
	}

	/**
	 * Funkce vrací formátovaný řetězec ze zadaného reálného čísla ve formátu '####.##' s daným počtem desetinných míst
	 *
	 * @param float $number
	 * @param number $dec
	 * @return string
	 */
	public static function value(string|int|float|null $number, int $dec = 0, bool $trim = false): string
    {
		if($number != '')
        {
            $result = number_format(floatval($number), $dec, '.', '');

            if($trim)
            {
                $result = rtrim($result, '0 ');
                $result = rtrim($result, ',.');
            }
        } else
			$result = '';

        return $result;
	}

	/**
	 * Funkce vrací formátovaný řetězec ze zadaného reálného čísla ve formátu '####,##' s daným počtem desetinných míst
	 *
	 * @param float $number
	 * @param number $dec
	 * @return string
	 */
	public static function valuestr(string|int|float|null $number, int $dec = 0, bool $trim = false): string
    {
		if($number != '')
        {
            $result = number_format(floatval($number), $dec, ',', '');

            if($trim)
            {
                $result = rtrim($result, '0 ');
                $result = rtrim($result, ',');
            }
        } else
			$result = '';

        return $result;
	}

	/**
	 * Funkce zaokrouhluje kladná čísla nahoru, záporná čísla dolů
	 *
	 * @param string value
	 * @return float
	 */
	public static function round_up(int|float|null $value): ?float
    {
        if($value != '')
        {
            $value = str_replace(' ','',$value);
            if($value != '')
            {
                $value = str_replace(',','.',$value);
                if($value < 0)
                    return floor($value);
                else
                    return ceil($value);
            } else
                return $value;
        } else
            return $value;
	}

	/**
	 * Funkce podstraňuje mezery ze zadaného řetězce čísla
	 *
	 * @param string $str
	 * @return ?string
	 */
	public static function strtonumber(null|string|int|float $str, ?string $default = null): ?string
    {
		if($str != '')
			return str_replace(' ','', $str);
		else
			return $default;
	}

	/**
	 * Funkce převádí řetězec na celé číslo int
	 *
	 * @param string $str
	 * @return ?int
	 */
	public static function strtoint(null|string|int $str, ?int $default = null): ?int
    {
		if($str != '')
			return intval(self::strtonumber($str));
		else
			return $default;
	}

	/**
	 * Funkce převádí řetězec na reálné číslo
	 *
	 * @param string $str
	 * @return ?float
	 */
	public static function strtofloat(null|string|int|float $str, ?float $default = null): ?float
    {
		if($str != '')
			return floatval(str_replace(',','.', self::strtonumber($str)));
		else
			return $default;
	}
}
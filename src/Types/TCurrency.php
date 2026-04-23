<?php

/**
 * Třída pro práci s měnou
 *
 * @name TCurrency
 * @author     vladimir.horky
 * @copyright  Vladimir Horky, 2018.
 * @version    1.1
 *
 * 1.1
 * updated format
 */

declare(strict_types=1);

class TCurrency
{
    /**
     * Funkce formátuje zadanou částku na řetězec ve tvaru # ###,## dle zadanéhp počtu desetinných míst (defaultně 2)
     *
     * @param string|float|int|null $amount
     * @param int $decimal
     * @param bool $trim
     * @return string
     */
	public static function format(string|float|int|null $amount, int $decimal = 2, bool $trim = false) :string
	{
        if($amount != '')
        {
            $result = number_format(floatval($amount), $decimal, ',', ' ');
            if($trim)
            {
                $result = rtrim($result, '0 ');
                $result = rtrim($result, ',.');
            }

            return $result;
        } else
            return '';
	}
}
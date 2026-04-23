<?php

/**
 * Výjimka a třída pro práci s cookies
 *
 * @version 1.0
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 */

declare(strict_types=1);

class TCookies
{
	const int COOKIE_TIMEOUT 	= 3600000;
	const int COOKIE_SESSION	= 0;
	const int COOKIE_DAY	 	= 86400;
	const int COOKIE_FOREVER	= 3600000;

    /**
     * Funkce pro dekódování cookie
     *
     * @param string|null $str
     * @return string
     */
	public static function decode(?string $str) :string
	{
		if($str == '')
			return '';
		else
			return base64_decode(mb_convert_encoding(urldecode($str), 'ISO-8859-1', 'UTF-8'));
	}

	/**
	 * Funkce pro zakódování hodnoty do cookie
	 *
	 * @param ?string $str
	 * @return string
	 */
	public static function encode(?string $str) :string
	{
		if($str == '')
			return '';
		else
			return urlencode(mb_convert_encoding(base64_encode($str), 'UTF-8', 'ISO-8859-1'));
	}

	/**
	 * Funkce vrací hodnotu požadované cookie
	 *
	 * @param string $name
	 * @return string
	 */
	public static function get(string $name, int $maxLength = 50) :string
	{
		$value = self::decode($_COOKIE[$name]);
        return substr($value, 0, $maxLength);
	}

    /**
     * Funkce ukládá hodnotu požadované cookie
     *
     * @param string $name
     * @param string $value
     * @param int $expires_or_options
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @return bool
     */
	public static function set(string $name, string $value = '', int $expires_or_options = 0, string $path = '', string $domain = '', bool $secure = false, bool $httponly = false): bool
    {
		return setCookie($name, self::encode($value), $expires_or_options, $path, $domain, $secure, $httponly);
	}

    public static function delete(string $name) :bool
    {
        return self::set($name, '', time() - 3600);
    }
}
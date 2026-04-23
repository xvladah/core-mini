<?php

/**
 * Třída pro zpracování příchozí URL včetně parametrů
 *
 * @name THttpRequest
 * @version 2.2
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 * 
 * version 2.2
 * - changes for parsing integer anf integerlist  
 *
 * version 2.1
 * - modify getIntegerList and getStringList
 *
 * verison 2.0
 * - added function get
 */

// declare(strict_types=1);

class THttpRequest
{
	public static function Encode(?string $text) :string
	{
		//return iconv(self::win1250, self::utf8, addSlashes($text));
		return addSlashes($text);
	}

	public static function Decode(?string $text) :string
	{
		//return iconv(self::utf8, self::win1250, stripSlashes($text));
		return stripSlashes($text);
	}

	public static function get(mixed &$var, ?string $input, array $values, int $max_length = 50, bool $urldecode = true) :bool
	{
		if($urldecode)
			$input = urldecode(trim((string)$input));
		else
			$input = trim((string)$input);

		$var = filter_var($input, FILTER_SANITIZE_STRING, ['max_length'=>$max_length]);

		foreach($values as $val)
			if((string) $val == (string)$var)
				return true;

		return false;
	}

	public static function getRegExp(mixed &$var, ?string $input, string $regexp) :bool
	{
		$input = trim((string)$input);
		if(preg_match($regexp, $input))
		{
			$var = $input;
			return true;
		} else {
			$var = '';
			return false;
		}
	}

	public static function getString(mixed &$var, ?string $input, int $max_length = 50, bool $urldecode = true) :bool
	{
		if($urldecode)
			$input = urldecode(trim((string)$input));
		else
			$input = trim((string)$input);

		if($max_length < 0)
			$max_length = 2147483647;

		if(strlen((string)$input) > $max_length)
			$input = substr($input, 0, $max_length);

		$var = filter_var($input, FILTER_SANITIZE_STRING, ['max_length'=>$max_length]);

		if($var !== false && strlen($input) > 0)
			return true;
		else {
			$var = '';
			return false;
		}
	}

	public static function getStringUTF(mixed &$var, ?string $input, int $max_length = 50, bool $urldecode = true) :bool
	{
		return self::getString($var, self::Decode($input), $max_length, $urldecode);
	}

	public static function getChar(mixed &$var, ?string $input, bool $urldecode = true) :bool
	{
		if($urldecode)
			$input = urldecode(trim((string)$input));
		else
			$input = trim((string)$input);

		$var = filter_var(substr(trim($input), 0, 1), FILTER_SANITIZE_STRING);
		if($var && strlen($input) === 1)
			return true;
		else {
			$var = '';
			return false;
		}
	}

	public static function getInteger(mixed &$var, ?string $input, int $min_range = 0, ?int $max_range = null) :bool
	{
		if($input != '')
		{
			$options = [
				'options'=>[]
			];

			if($min_range != '')
				$options['options']['min_range'] = $min_range;

			if($max_range != '')
				$options['options']['max_range'] = $max_range;

			$var = filter_var(intval($input), FILTER_VALIDATE_INT, $options);
			if((String) $var == '' || $var === false)
			{
				$var = 0;
				if((int)$input == $var && (int)$input >= $min_range && (int)$input <= $max_range)
					return true;
				else
					return false;
			} else
				return ((string)$input == (string)$var);
		} else {
			$var = 0;
			return false;
		}
	}

	public static function getId(mixed &$var, ?string $input, int $min_range = 1, ?int $max_range = null) :bool
	{
		return self::getInteger($var, $input, $min_range, $max_range);
	}

	public static function getFloat(&$var, $input, $min_range = 0, $max_range = '') :bool
	{
		$input = str_replace(',','.',str_replace(' ','',trim($input)));

		$pattern = '/^[+-]?[0-9]+(\.[0-9]*)?$/';
		//$valid = (!is_bool($input) && (is_double($input) || is_integer($input) || preg_match($pattern, $input)));
		$valid = preg_match($pattern, $input);

	    if($valid)
	    {
			if($min_range != '')
				$valid = $input >= $min_range;

			if($valid && $max_range != '')
				$valid = $input <= $max_range;

			$var = $input;
			return $valid;
	    } else {
	    	$var = NAN;
	    	return false;
	    }
	}

	public static function getDate(mixed &$var, ?string $input, string $out_format = 'd.m.Y') :bool
	{
		return self::getDateTime($var, $input, $out_format);
	}

	public static function getTime(mixed &$var, ?string $input, string $out_format = 'H:i:s') :bool
	{
		return self::getDateTime($var, $input, $out_format);
	}

    public static function getDateTime(mixed &$var, ?string $input, string $out_format = 'd.m.Y H:i:s') :bool
    {
        $input = trim($input);
        if($input != '')
        {
            if(strlen($input) > 19)
                $input = substr($input, 0, 19);

            // Bezpečná konverze vstupu na timestamp
            $ts = strtotime($input);
            if ($ts === false) {
                $var = '';
                return false;
            }

            // Správná funkce je date() (lowercase) a vrací vždy string
            $var = date($out_format, $ts);
            return true;
        } else
            return false;
    }

	public static function getYear(mixed &$var, ?string $input) :bool
	{
		return self::getInteger($var, $input, 1899, 2100);
	}

	public static function getMonth(mixed &$var, ?string $input) :bool
	{
		return self::getInteger($var, $input, 1, 12);
	}

	public static function getDay(mixed &$var, ?string $input) :bool
	{
		return self::getInteger($var, $input, 1, 31);
	}

	public static function getIntegerList(mixed &$var, null|string|array $input, int $max_length = -1, int $min_range = 0, ?int $max_range = null, string $delimiter = ',', bool $urldecode = true) :bool
	{
		$var = [];
		
		if(is_array($input))
			$input = implode(',', $input);

		if($input != '')
		{		
			$l = strlen((string)$input);
			
			if($urldecode)
				$input = urldecode(trim((string)$input));
			else
				$input = trim((string)$input);
						
			if($max_length < 0)
				$max_length = 2147483647;
							
			if($l > $max_length)
				$input = substr($input, 0, $max_length);
				
			$options = [
				'options' => []
			];
			
			if($min_range != '')
				$options['options']['min_range'] = $min_range;
				
			if($max_range != '')
				$options['options']['max_range'] = $max_range;

			$values = explode($delimiter, $input);
			
			foreach($values as $value)	
			{				
				$val = intval($value);
				$variable = filter_var($val, FILTER_VALIDATE_INT, $options);

				if((string)$variable == '' || $variable === false)			
				{
					$variable = 0;
					if($val != $variable || $val < $min_range || $val > $max_range)
						return false;
					else
						$var[] = $variable;
				} else {
					if ($value == (string)$variable)
						$var[] = $variable;
					else
						return false;
				}
			}

			$result = true;
		} else
			$result = false;
		
		return $result;
	}
	
	public static function getStringList(mixed &$var, null|string|array $input, int $max_length = -1, string $delimiter = ',', bool $urldecode = true) :bool
	{
		$var = [];
		
		if(is_array($input))
			$input = implode(',', $input);

		if($input != '')
		{
			$l = strlen((string)$input);

			if($urldecode)
				$input = urldecode(trim((string)$input));
			else
				$input = trim((string)$input);
	
			if($max_length < 0)
				$max_length = 2147483647;

			if($l > $max_length)
				$input = substr($input, 0, $max_length);
							
			$variable = filter_var($input, FILTER_SANITIZE_STRING, ['max_length'=>$max_length]);
			if($variable !== false && strlen((string)$variable) > 0)
			{
				$var = explode($delimiter, $variable);
				$result = true;
			} else
				$result = false;
		} else
			$result = false;

		return $result;
	}

    public static function getBrowser(?string $agent = null) :string
    {
       // $agent = (string)($agent ?? ($_SERVER['HTTP_USER_AGENT'] ?? ''));
        $agent = trim($agent);
       /* if ($agent === '') {
            return 'Neurčený webový prohlížeč';
        }*/

        $patterns = [
            // MSIE legacy
            '/\bMSIE[ \d\.]+/i'               => null, // matched value used directly
            // IE11 (Trident/7 + rv)
            '/\bTrident\/7\.\d.*\brv:(\d+\.\d+)/i' => 'MSIE $1',
            // Legacy Edge (EdgeHTML)
            '/\bEdge\/([\d\.]+)/i'            => 'Edge $1',
            // Chromium Edge
            '/\bEdg\/([\d\.]+)/i'             => 'Edge $1',
            // Opera (Chromium)
            '/\bOPR\/([\d\.]+)/i'             => 'Opera $1',
            // Opera (starší)
            '/\bOpera[\/ ]([\d\.]+)/i'        => 'Opera $1',
            // Firefox (desktop + iOS FxiOS)
            '/\bFxiOS\/([\d\.]+)/i'           => 'Firefox iOS $1',
            '/\bFirefox\/([\d\.]+)/i'         => 'Firefox $1',
            // Chrome (Android/desktop + iOS CriOS)
            '/\bCriOS\/([\d\.]+)/i'           => 'Chrome iOS $1',
            '/\bChrome\/([\d\.]+)/i'          => 'Chrome $1',
            // Safari (vyhnutí se Chrome: musí obsahovat Version/x a Safari)
            '/\bVersion\/([\d\.]+).*Safari\/[\d\.]+/i' => 'Safari $1',
            // Maxthon, Konqueror, Mobile, Python
            '/\bMaxthon\/([\d\.]+)/i'         => 'Maxthon $1',
            '/\bKonqueror\/?([\d\.]*)/i'      => 'Konqueror $1',
            '/\bMobile\/?([\d\.]*)/i'         => 'Mobile $1',
            // Bots/skripty (opravný rozsah znaků A-Za-z)
            '/\bpython[-A-Za-z\/\d\.]*/i'     => null
        ];

        foreach ($patterns as $regex => $format) {
            if (preg_match($regex, $agent, $m)) {
                $raw = $m[0];
                if ($format === null) {
                    // Vrátí nalezený token bez lomítek
                    return str_replace('/', ' ', $raw);
                }
                // Dosadí skupiny do formátu
                for ($i = 1; $i < count($m); $i++) {
                    $format = str_replace('$'.$i, $m[$i], $format);
                }
                return $format;
            }
        }

        return (($agent == '') ? 'Unknown agent' : $agent);
    }

	public static function badBrowser() :array
	{
		return ['MSIE 6.0','MSIE 6','MSIE 7.0','MSIE 7','MSIE 8.0','MSIE 8'];
	}

	public static function getOS(?string $agent = null) :string
	{
		if($agent == '')
			return 'Unknown OS';

		$os_array =	[
			'/windows nt 10/i'     	=>  'Windows 10',
			'/windows nt 6.3/i'     =>  'Windows 8.1',
			'/windows nt 6.2/i'     =>  'Windows 8',
			'/windows nt 6.1/i'     =>  'Windows 7',
			'/windows nt 6.0/i'     =>  'Windows Vista',
			'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     =>  'Windows XP',
			'/windows xp/i'         =>  'Windows XP',
			'/windows nt 5.0/i'     =>  'Windows 2000',
			'/windows me/i'         =>  'Windows ME',
			'/win98/i'              =>  'Windows 98',
			'/win95/i'              =>  'Windows 95',
			'/win16/i'              =>  'Windows 3.11',
			'/macintosh|mac os x/i' =>  'Mac OS X',
			'/mac_powerpc/i'        =>  'Mac OS 9',
			'/linux/i'              =>  'Linux',
			'/ubuntu/i'             =>  'Ubuntu',
			'/iphone/i'             =>  'iPhone',
			'/ipod/i'               =>  'iPod',
			'/ipad/i'               =>  'iPad',
			'/android/i'            =>  'Android',
			'/blackberry/i'         =>  'BlackBerry',
			'/webos/i'              =>  'Mobile'
		];

		$os_platform = 'Unknown OS "'.$agent.'"';
		foreach($os_array as $regex => $value)
			if(preg_match($regex, $agent))
			{
				$os_platform = $value;
				break;
			}

		return $os_platform;
	}
}

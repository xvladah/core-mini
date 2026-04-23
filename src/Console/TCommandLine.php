<?php

/**
 * Třída pro parsování příkazové řádky
 *
 * @name TCommandLine
 * @version 1.1
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 * 
 * version 1.1.
 * -added function each, for php8 not supported
 */

declare(strict_types=1);

class TCommandLine
{
	private static function each(array &$arr): false|array
    {
		$key = key($arr);
		$result = ($key === null) ? false : [$key, current($arr), 'key' => $key, 'value' => current($arr)];
		next($arr);
		return $result;
	}

	public static function parseArgs(array $params, array $noopt = []) :array
	{
		$result = [];
	
		reset($params);
		while(list($tmp, $p) = self::each($params))
        {
            if ($p[0] === '-')
            {
                $pname = substr($p, 1);
                $value = true;
                if ($pname[0] === '-')
                {
                    $pname = substr($pname, 1);
                    if (str_contains($p, '='))
                        list($pname, $value) = explode('=', substr($p, 2), 2);
                }

                $nextparm = current($params);
                if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm[0] != '-')
                	list($tmp, $value) = self::each($params);

                $result[$pname] = $value;
            } else
                $result[] = $p;
        }

        return $result;
	}
}

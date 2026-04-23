<?php

class TSQLGenerator extends TSQLParser
{	
	public static function StrDecode(?string $str): ?string
	{
		$str = mysqli_real_escape_string(null, trim($str));
		$str = str_replace("&#10;", "\r\n", $str);
		$str = str_replace('&apos;', '\'', $str);
		$str = str_replace('&quot;', '\"', $str);
		$str = str_replace('&amp;', '&', $str);
		$str = str_replace('&lt;', '<', $str);
        return str_replace('&gt;', '>', $str);
	}
	
	public static function StrEncode(?string $str): ?string
	{
		$str = str_replace('&', '&amp;', $str);
		$str = str_replace("\r\n", "&#10;", $str);
		$str = str_replace('\'', '&apos;', $str);
		$str = str_replace('"', '&quot;', $str);
		$str = str_replace('<', '&lt;', $str);
        return str_replace('>', '&gt;', $str);
	}
	
	public function Number(?string $value, int $default = 0): array|int|string
    {
		$value = trim($value);
		if($value == '')
			return $default;
		else
			return str_replace(',','.',str_replace(' ','',$value));
	}
	
	public function NumberNull(?string $value): array|string
    {
		$value = trim($value);
		if($value == '')
			return 'null';
		else
			return str_replace(',','.',str_replace(' ','',$value));
	}
	
	public function Str(?string $value, string $default = ''): string
    {
		if($value == '')
			return '"'.$default.'"';
		else
			return '"'.self::StrDecode($this->convertStr($value)).'"';
	}
	
	public function StrNull(?string $value): string
    {
		if($value == '')
			return 'null';
		else
			return '"'.self::StrDecode($this->convertStr($value)).'"';
	}
	
	public function Date(?string $value, string $default = '1899-12-31'): string
    {
		if($value == '')
			return '"'.$default.'"';
		else
			return '"'.self::StrToDate($value).'"';
	}
	
	public function DateNull(?string $value): string
    {
		if($value == '')
			return 'null';
		else
			return '"'.self::StrToDate($value).'"';
	}
	
	public function Time(?string $value, string $default = '10:00:00'): string
    {
		if($value == '')
			return '"'.$default.'"';
		else
			return '"'.self::StrToTime($value).'"';
	}
	
	public function TimeNull(?string $value): string
    {
		if($value == '')
			return 'null';
		else
			return '"'.self::StrToTime($value).'"';
	}
	
	public function DateTime(?string $value, string $default = '1899-12-31 00:00:00'): string
    {
		if($value == '')
			return '"'.$default.'"';
		else
			return '"'.self::StrToDateTime($value).'"';
	}
	
	public function DateTimeNull(?string $value): string
    {
		if($value == '')
			return 'null';
		else
			return '"'.self::StrToDateTime($value).'"';
	}
	
	public static function StrToTime(?string $str): string
    {
		$cas = preg_split('/:/', $str);
		return implode(':', $cas);
	}
	
	public static function StrToDate(?string $str): string
    {
		list($day, $month, $year) = preg_split('/[\.-\/]/', trim($str));
		return intval($year).'-'.$month.'-'.$day;
	}
	
	public static function StrToDateTime(?string $str): string
    {
		$val = explode(' ', Trim($str));
		if(!empty($val[1]))
			return self::StrToDate($val[0]).' '.$val[1];
		else
			return self::StrToDate($val[0]);
	}
	
}

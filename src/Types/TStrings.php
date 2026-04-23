<?php

/**
 *  Třída pro práci s řetězci
 *
 * @name TStrings
 * @version 1.9
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2019
 * 
 * version 1.9
 * - added isUTF
 * - added isEncoding
 *
 * version 1.8
 * - change ifElseStr
 * - change ifStr 
 *
 * version 1.7
 * - added TextToComments
 *
 * version 1.6
 * - changed CommentsToHtml
 * - changed CommentsToText
 *
 * version 1.5
 * - changed CommentsToText
 *
 * version 1.4
 * - added StrEncodeToLine
 * - added StrEncodeToHtml
 * - added concateText
 * - added explodeText
 *
 * version 1.3
 * - added function Null
 * - added function Quote
 *
 * version 1.2
 * - added function StrToText
 *
 * version 1.1
 * - added function ucfirst
 */

declare(strict_types=1);

class TStrings
{
    public static function str_not_containsi(string $value, array $array): bool
    {
        foreach($array as $item)
        {
            if(stripos($value, $item) !== false)
                return false;
        }

        return true;
    }

	public static function isUTF(?string $contents): bool
    {
		return self::isEncoding($contents, 'utf-8');
	}
	
	public static function isEncoding(?string $contents, string $encoding = 'utf-8'): bool
    {
		return mb_check_encoding($contents, $encoding);
	}
	
 	public static function StrToLower(string $str): string
    {
        return mb_strtolower($str);
    }

    public static function StrToUpper(string $str): string
    {
        return mb_strtoupper($str);
    }

    public static function StrDecode(string $str, array $changes = []): string
    {
        $zal = ["&#10;"=>"\r\n", '&apos;'=>'\'', '&quot;'=>'\"', '&amp;'=>'&', '&lt;'=>'<', '&gt;'=>'>'];
        foreach($changes as $key => $value)
            $zal[$key] = $value;

        $str = addSlashes($str);

        foreach($zal as $key => $value)
            $str = str_replace($key, $value, $str);

        return $str;
    }

    public static function StrEncode(string $str, array $changes = []): string
    {
        $zal = ['&'=>'&amp;', '<'=>'&lt;', '>'=>'&gt;', '\''=>'&apos;', '"'=>'&quot;', "\r\n"=>"&#10;", "\n"=>"&#10;"];
        foreach($changes as $key => $value)
            $zal[$key] = $value;

        foreach($zal as $key => $value)
            $str = str_replace($key, $value, $str);

        return $str;
    }
    
    /*
     * Prevede text z databaze na prosty text, ktery lze zobrazit v tabulce na jednom radku
     * 
     */
    public static function StrEncodeToLine(?string $str, array $changes = []): string
    {  	
    	$zal = ['&#10;' => ' ', "\r\n"=>' ', '& '=>'&amp;', '<'=>'&lt;', '>'=>'&gt;', '\''=>'&apos;', '"'=>'&quot;'];
    	foreach($changes as $key => $value)
    		$zal[$key] = $value;

        if($str != '')
        {
    	    foreach($zal as $key => $value)
    		    $str = str_replace($key, $value, $str);
        } else
            $str = '';
    			
    	return $str;
    }
    
    /*
     * Prevede text z databaze na HTML text, ktery lze zobrazit v dialogu
     */
    public static function StrEncodeToHtml(?string $str, array $changes = []): string
    {
    	$zal = ['& '=>'&amp; ', '<'=>'&lt;', '>'=>'&gt;', '\''=>'&apos;', '"'=>'&quot;', '&#10;' => '<br />', "\r\n"=>'<br />'];
    	foreach($changes as $key => $value)
    		$zal[$key] = $value;

        if($str != '')
        {
    	    foreach($zal as $key => $value)
    		    $str = str_replace($key, $value, $str);
        } else
            $str = '';
    			
    	return $str;
    }

    public static function StrEncodeToAttr(?string $str, array $changes = []): string
    {
        $zal = ['& '=>'&amp; ', '<'=>'&lt;', '>'=>'&gt;', '\''=>'&apos;', '"'=>'&quot;', '&#10;' => "\n", "\r\n"=>"\n"];
        foreach($changes as $key => $value)
            $zal[$key] = $value;

        if($str != '')
        {
            foreach($zal as $key => $value)
                $str = str_replace($key, $value, $str);
        } else
            $str = '';

        return $str;
    }

	public static function StrToHTML(?string $str): string
	{
        if($str != '')
        {
            $str = str_replace("\r\n", '<br />', $str);
            $str = str_replace("\n", '<br />', $str);
            $str = str_replace("\t", '&nbsp; &nbsp; &nbsp;', $str);

            return str_replace('&#10;', '<br />', $str);
        } else
            return '';
	}

	/*
	 * Prevede text z databaze na prosty text a odstrani vsechny entity
	 */
	public static function StrToText(?string $string): string
	{
        if($string != '')
        {
            $zal = ["&#10;"=>"\r\n", '&apos;'=>"'", '&quot;'=>'"', '&amp;'=>'&', '&lt;'=>'<', '&gt;'=>'>'];

            foreach($zal as $key => $value)
                $string = str_replace($key, $value, $string);

            return $string;
        } else
            return '';
	}
	
	public static function StrToVal(?string $str): string
	{
		if(is_null($str))
			return '';
		else
			return '';
	}

	public static function StrToHex(?string $string): string
    {
	    $hex = '';

        if($string != '')
        {
	        for ($i = 0; $i < strlen($string); $i++)
	            $hex .= dechex(ord($string[$i]));
        }

	    return $hex;
	}

	public static function HexToStr(?string $hex): string
    {
	    $string = '';

        if($hex != '')
        {
	        for ($i = 0; $i < strlen($hex)-1; $i += 2)
	            $string .= chr(hexdec($hex[$i].$hex[$i+1]));
        }

	    return $string;
	}
	public static function Normalize(?string $str) :string
	{
        if($str != '')
		    return iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        else
            return '';
	}

	public static function ifStr(bool $podminka, ?string $str): ?string
	{
		return self::ifelseStr($podminka, $str, '');
	}

	public static function ifElseStr(bool $podminka, ?string $str1, ?string $str2): ?string
	{
		if($podminka)
			return $str1;
		else
			return $str2;
	}

	public static function ucfirst(string $string, bool $lowercase = false) :string
	{
        if($string != '')
        {
            $first = mb_strtoupper(mb_substr($string, 0, 1));

            if($lowercase)
                $other = mb_strtolower(mb_substr($string, 1));
            else
                $other = mb_substr($string, 1);
        } else {
            $first = '';
            $other = '';
        }

		return $first.$other;
	}

	public static function StrNormalize(?string $str) :string
	{
		if($str != '')
		{
			$table_transform = [
					//' '=>'_',

					'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A',
					'Þ'=>'B',
					'Æ'=>'C', 'Ć'=>'C', 'Ç'=>'C', 'Č'=>'C',
					'Ď'=>'D', 'Đ'=>'Dj',
					'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ě'=>'E',
					'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I',
					'Ľ'=>'L',
					'Ň'=>'N', 'Ñ'=>'N',
					'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
					'Ř'=>'R', 'Ŕ'=>'R',
					'Š'=>'S',
					'Ť'=>'T',
					'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U',
					'Ý'=>'Y', 'Ÿ'=>'Y',
					'Ž'=>'Z',

					'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a',
					'þ'=>'b',
					'æ'=>'c', 'ć'=>'c', 'ç'=>'c', 'č'=>'c',
					'ď'=>'d', 'đ'=>'dj',
					'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ě'=>'e',
					'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
					'ľ'=>'l',
					'ň'=>'n', 'ñ'=>'n',
					'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ð'=>'o',
					'ř'=>'r', 'ŕ'=>'r',
					'š'=>'s', 'ß'=>'Ss',
					'ť'=>'t',
					'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ů'=>'u',
					'ý'=>'y', 'ÿ'=>'y',
					'ž'=>'z'
			];

            return strtr(trim($str), $table_transform);
		} else
			return '';
	}
	
	public static function removeSpecialChars(string $str, string $compensation = ''): string
	{
	    return str_replace(['\'', '/', '\\', '"', ',' , ';', '<', '>', '’', '‘', '“', '”', '«', '»', '„'], $compensation, $str);
	}

	public static function StrCaseCmp(?string $str1, ?string $str2) :bool
	{
		return self::StrNormalize($str1) === self::StrNormalize($str2);
	}

	public static function StrCaseCmpi(?string $str1, ?string $str2) :bool
	{
		return mb_strtolower(self::StrNormalize($str1)) === mb_strtolower(self::StrNormalize($str2));
	}
	
	public static function StrToCurrency(?string $price, int $decimals = 2) :string
	{
		if($price != '')
			return number_format(floatval($price), $decimals, ',', ' ');
		else
			return '';
	}

	public static function StrToFloat(?string $str) :string
	{
		if($str != '')
			return str_replace(',','.', str_replace(' ','', $str));
		else
			return '';
	}

	public static function getDefaultStr(?string $str, string $default = '-') :string
	{
		if($str == '')
			return $default;
		else
			return '';
	}

	public static function StrShorten(?string $str, int $length = 100): string
    {
        if($str != '')
        {
		    if(mb_strlen($str) > $length)
    			return mb_substr($str, 0, ($length - 3)).'...';
    		else
	    		return $str;
        } else
            return '';
	}
	
	public static function Quote(?string $str): string
    {
	    if($str != '')
	        return '"'.addSlashes($str).'"';
	    else
	        return '';
	}
	
	public static function Null(?string $str): ?string
	{
	    if($str != '')
	        return $str;
	    else
	        return null;
	}
	
	public static function CommentsToAPI(?string $source): array
    {
		$result = [];
		
		if($source != '')
		{
			$pole = explode('|', $source);
			
			$index = 0;
			$i = 0;
			foreach($pole as $item)
			{
				switch(++$i)
				{
					case 1 : $result[$index]['name'] = $item; break;
					case 2 : $result[$index]['date'] = $item; break;
					case 3 : $result[$index]['text'] = $item; $index++; $i = 0; break;
				}
			}
		}
		
		return $result;
	}
	
	/*
	 * Prevede slozeny text s databaze na HTML, ktere lze zobrazit treba v dialogu
	 */
	public static function CommentsToHtml(?string $source): string
    {
		$result = '';
		
		if($source != '')
		{
			$pole = explode('|', $source);
			
			$substr = '';
			$i = 0;
			foreach($pole as $item)
			{
				switch(++$i)
				{
					case 1	: 	if($item != '')
									$substr .= '----- '.$item;
						break;
						
					case 2	: 	if($item != '')
									$substr .= ' ----- '.$item.' -----';
						break;

					default	: 	if($result != '')
									$result .= '<br /><br />';
						
								if($substr != '')
									$result .= $substr .'<br />'.trim(self::StrEncodeToLine($item));
								else
									$result .= trim(self::StrEncodeToLine($item));
									
								$substr = '';
								$i = 0;
						break;
				}
			}
		}
		
		return $result;
	}

	/*
	 * Prevede slozeny text z databaze na obycejny text, ktery lze zobrazit v textarea
	 */
	public static function CommentsToText(?string $source): string
	{
		$result = '';
		
		if($source != '')
		{
			$pole = explode('|', $source);
			
			$substr = '';
			$i = 0;
			foreach($pole as $item)
			{
				switch(++$i)
				{
					case 1	: 	if($item != '')
									$substr .= '----- '.$item;
						break;

					case 2	: 	if($item != '')
									$substr .= ' ----- '.$item.' -----';
						break;

					default	: 	if($result != '')
									$result .= "\r\n\r\n";

								if($substr != '')
							  		$result .= $substr ."\r\n".trim(self::StrToText($item));
							  	else	
							  		$result .= trim(self::StrToText($item));

							  	$substr = '';
							  	$i = 0;
						break;
				}
			}
		}
		
		return $result;
	}
	
	public static function TextToComments(?string $source): string
	{
		$result = '';
		
		$pole = explode('-----', $source);
		foreach($pole as $item)
		{
			$item = trim($item);
			
			if($item != '')
				if($result != '')
					$result .= '|'.$item;
				else
					$result .= $item;
		}
		
		return $result;
	}
	
	public static function CommentToHtml(?string $text, string $author, string $datumpz): string
    {
		return '----- '.$author.' ----- '.$datumpz.' -----'."\r\n".trim(self::StrToText($text));
	}
	
	/*
	 * Prevede slozeny text na zobrazitelny v jednom radku v tabulce
	 */
	public static function CommentsToLine(?string $str): string
    {
		$result = '';
		if($str != '')
		{
			$pole = explode('|', $str);
			$i = 0;
			foreach($pole as $item)
			{
				switch(++$i)
				{
					case 1:
					case 2:
                        break;

					default	:
							if($result != '')
								$result .= ', ';

							$result .= trim(self::StrEncodeToLine($item));
							$i = 0;
						break;
				}
			}
		}
		
		return $result;
	}
	
}

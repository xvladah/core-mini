<?php

// declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

/**
 * Třída pro odesílání hlavičky HTTP/1.1
 *
 * @name THttpResponse
 * @version 1.4
 * @author     vladimir.horky
 * @copyright  Vladimir Horky, 2022.
 * 
 * -update ParamsToStr 
 * -added ParamsToStr
 */


class THttpResponse
{
	public const string win1250 	= 'windows-1250';
    public const string utf8		= 'utf-8';

    public const int MAX_LOG_SIZE = 4096;
    public const int MAX_LOG_ITEM_SIZE = 255;

	public static function Encode(?string $str) :?string
	{
        if($str != '')
        {
            $str = str_replace("\r\n", "&#10;", $str);
            $str = str_replace("\n", "&#10;", $str);
            $str = str_replace('\'', '&apos;', $str);

            $str = preg_replace('/\s+/', ' ', $str);

            //return iconv(self::win1250, self::utf8, addSlashes($str));
            return addSlashes($str);
       } else
           return $str;
	}

	public static function RemoveSpecials(?string $str) :?string
	{
        if($str != '')
        {
            $str = str_replace("\r\n", "&#10;", $str);
            $str = str_replace("\n", "&#10;", $str);
            $str = str_replace('\'', '&apos;', $str);
            return str_replace('"', '&quot;', $str);
        } else
            return $str;
	}

	public static function HtmlSpecials(?string $str) :?string
	{
        if($str != '')
        {
            $str = str_replace("\r\n", "<br />", $str);
            $str = str_replace("\n", "<br />", $str);
            $str = str_replace("&#10;", "<br />", $str);
            $str = str_replace("\t", '&nbsp; &nbsp; &nbsp;', $str);

            $str = str_replace('\'', '&apos;', $str);
            return str_replace('"', '&quot;', $str);
        } else
            return $str;
    }

    #[NoReturn] public static function SendCode(int $http_code, ?string $http_message = null): void
    {
        header('HTTP/1.1 '.$http_code.' '.THttpConsts::getHttpCodeDesc($http_code));
        die($http_message);
	}

	public static function SendHeader(string $encoding = 'utf-8', string $type = 'html'): void
    {
		header('Content-type: text/'.$type.';charset='.$encoding);
		header('Expires: Mon, 1 Jan 2011 10:00:00 GMT');
		header('Cache-Control: s-maxage=0,max-age=0,must-revalidate');
	}

	public static function SendHeaderPlainText(string $encoding = 'utf-8', string $type = 'plain'): void
    {
		self::SendHeader($encoding, $type);
	}

	#[NoReturn] public static function SendLocation(string $location): void
    {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.$location);
		die();
	}

	#[NoReturn] public static function SendContent(string $content, string $mime = 'application/json', string $encoding = 'utf-8'): void
    {
		header('Content-type: '.$mime.';charset='.$encoding);
		header('Expires: Mon, 1 Jan 2011 10:00:00 GMT');
		header('Cache-Control: s-maxage=0,max-age=0,must-revalidate');
//		header('Access-Control-Allow-Origin: *');

		die($content);
	}

	#[NoReturn] public static function SendHTML(string $content, string $encoding = 'utf-8'): void
    {
		header('Content-type: text/html;charset='.$encoding);
		header('Expires: Mon, 1 Jan 2011 10:00:00 GMT');
		header('Cache-Control: s-maxage=0,max-age=0,must-revalidate');
		
		die($content);
	}
	
	#[NoReturn] public static function SendJSON($content, string $encoding = 'utf-8'): void
    {
		header('Content-type: application/json;charset='.$encoding);
		header('Expires: Mon, 1 Jan 2011 10:00:00 GMT');
		header('Cache-Control: s-maxage=0,max-age=0,must-revalidate');
//		header('Access-Control-Allow-Origin: *');		
		
		if(is_array($content))
			die(json_encode($content));
		else
			die($content);
	}
	
	#[NoReturn] public static function SendException(int $code, Exception $e, string $encoding = 'utf-8'): void
    {
		header('Content-type: application/json;charset='.$encoding);
		header('Expires: Mon, 1 Jan 2011 10:00:00 GMT');
		header('Cache-Control: s-maxage=0,max-age=0,must-revalidate');

		if($code > 0 )
			$code *= -1;
		
		die(json_encode([
			'result'=> $code,
			'err'	=> TErrorsEx::formatException($e)
		]));
	}

	public static function SendContentAsFile(string $filename, string $content): void
    {
		Header('Pragma: public');
		Header('Expires: 0');
		Header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		Header('Cache-Control: private', false);
		Header('Content-Transfer-Encoding: binary');
		Header('Content-Type: application/octet-stream');
		Header('Content-Length: '.strlen($content));
		Header('Content-Disposition: inline; filename="'.basename($filename).'"');

		echo $content;
	}
	
	private static function is_json($string): bool
    {
		return ((is_string($string) &&
			(is_object(json_decode($string)) ||
				is_array(json_decode($string, true))))) ? true : false;
	}
	
	public static function ParamsToStr(bool $prefix = true, string $prefix_text = ', ')
	{
		$result = '';
		
		if(count($_GET) > 0)
			$result = 'GET:'.json_encode($_GET);
		
		if(count($_POST) > 0)
		{
			if($result != '')
				$result .= ', ';

			$_POST2 = [];	
			foreach($_POST as $key => $value)	
			{
                if(is_array($value))
                    $value = json_encode($value, true);

				if(strlen($value) < self::MAX_LOG_ITEM_SIZE)
					$_POST2[$key] = $value;	
				else
					$_POST2[$key] = substr($value, 0, self::MAX_LOG_ITEM_SIZE-3).'...';
			}

			$result .= 'POST:'.json_encode($_POST2);
		} else {
			$content = file_get_contents('php://input', 'r'); 
			if($content != '')
			{
				if($result != '')
					$result .= ', ';
					
				if(strlen($content) <  self::MAX_LOG_SIZE)
					$result .= 'BODY:'.$content;
				else					
					if(self::is_json($content))
					{
						$json = json_decode($content, true);
						
						if(json_last_error() == JSON_ERROR_NONE)
						{
							$BODY = []; 
							foreach($json as $key => $value)
							{
                                if(is_array($value))
                                    $value = json_encode($value, true);

								if(strlen($value) < self::MAX_LOG_ITEM_SIZE)
									$BODY[$key] = $value;
								else
									$BODY[$key] = substr($value, 0, self::MAX_LOG_ITEM_SIZE-3).'...';
							}
							
							$result .= 'BODY:'.json_encode($BODY);							
						} else 
							$result .= 'BODY:'.substr($content, 0, self::MAX_LOG_SIZE-3).'...';
					} else 
						$result .= 'BODY:'.substr($content, 0, self::MAX_LOG_SIZE-3).'...';
			}
		}

		if(count($_FILES) > 0)
		{
			if($result != '')
				$result .= ', ';
				
			$result .= 'FILES:'.json_encode($_FILES);
		}
		
		if($prefix)
			if($result != '')
				$result = $prefix_text.$result;
		
		return $result;
	}
}
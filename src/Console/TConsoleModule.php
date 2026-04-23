<?php

/**
 * Abstraktní třída pro konzolu
 *
 * @name		TConsoleModule
 * @version 	1.0
 * @author		vladimir.horky
 * @copyright	Vladimir Horky, 2019
 */

declare(strict_types=1);

abstract class TConsoleModule implements IConsoleModule
{
	const int ERR_FILE_NOT_FOUND	= -1000;
    const int ERR_MODULE_NOT_FOUND 	= -1010;
    const int ERR_CLASS_NOT_FOUND 	= -1100;
    const int ERR_ACTION_NOT_FOUND 	= -1200;
    const int ERR_PARAM_NOT_FOUND 	= -1300;
    const int ERR_ACTION_FAILED		= -2000;
	
    const int SUCCESS_CODE_OK 		= 1000;
    const int SUCCESS_CODE_PRINT 	= 2000;
    
	public array $params;
	
	public function __construct(array $params = [])
	{
		$this->params = $params;
	}
	
	public abstract function execute(string &$output) :int;
	public abstract function help() :string;
	
	public function actions() :array
	{
		return static::MODULE_ACTIONS;
	}
	
	/**
	 * Funkce generuje následující název souboru s pořadovým číslem
	 *
	 * @param string $filename
	 * @return string
	 */
	public static function getNextFileName(string $filename) :string
	{
		if(file_exists($filename))
		{
			$i = mb_strrpos($filename, '.');
			if($i > 0)
			{
				$basename 	= mb_substr($filename, 0, $i);
				$extension 	= mb_substr($filename, $i + 1);
			} else {
				$basename 	= $filename;
				$extension 	= '';
			}
			
			$i = 1;
			while(file_exists($filename = ($basename.'('.$i.').'.$extension)))
				$i++;
		}
		
		return $filename;
	}
	
	/**
	 * Funkce vraci nazev korenoveho adresare
	 *
	 * @param string $homedir
	 * @return string
	 */	
	public static function getProjectName() :string
	{	
		$pole = explode(DIRECTORY_SEPARATOR, str_replace(['/', '\\'], DIRECTORY_SEPARATOR, mb_strtolower(__DIR__)));
		if(count($pole) > 2)
			return $pole[count($pole)-3];			
		else
			return 'Unknown';
	}

	/**
	 * Funkce generuje text s barevnym pozadim a textem
	 *
	 * @param string $msg
	 * @param string $background
	 * @param string $text
	 * @return string
	 */
	public static function get(string $msg, string $background, string $text = ''): string
    {
		$msg = '  '.$msg.'  ';
		$strspc = str_pad('', strlen($msg), ' ');
		return PHP_EOL.$text.$background.$strspc.PHP_EOL.$msg.PHP_EOL.$strspc."\033[0m".PHP_EOL.PHP_EOL;
	}
	
	/**
	 * Funkce generuje text s cervenym pozadim
	 *
	 * @param string $msg
	 * @return string
	 */
	public static function geterr(string $msg): string
    {
		return self::get($msg, "\033[41m");
	}
	
	/**
	 * Funkce generuje text s modrym pozadim
	 *
	 * @param string $msg
	 * @return string
	 */
	public static function getinfo(string $msg): string
    {
		return self::get($msg, "\033[44m");
	}
	
	/**
	 * Funkce generuje text se zelenym pozadim a cernym textem
	 *
	 * @param string $msg
	 * @return string
	 */
	public static function getsuccess(string $msg): string
    {
		return self::get($msg, "\033[42m", "\033[0;30m");
	}
}
<?php

/**
* Třída pro generování jazykové mutace
*
* @name TLang and TLangParser
* @version 3.1
* @author     vladimir.horky
* @copyright  Vladimir Horky, 2018.
*
* version 3.1
* added array before $items
*
* version 3.1
* some corrections of file parsing
*
* version 3.0
* new function for loading phrases from dictionary
*
* version 2.0
* changes in result type all functions
*
* version 1.1
* Correction function LoadFromPHP
*/

declare(strict_types=1);

class TLang
{
    public static ?TLang $instance = null;
    public array $items 		 = [];

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @return TLang - the *Singleton* instance.
     */
    public static function getInstance() :TLang
    {
        if(static::$instance === null)
            static::$instance = new static();

        return static::$instance;
    }

    /**
     * Funkce vrací požadovaný text z aktuální jazykové mutace
     *
     * @param string $textid
     * @param int $lang
     * @param string $default
     * @return string
     */
    public function __($sysid, $default)
    {
        if($sysid != '')
        {
            $text = $this->items[$sysid];
            if($text === null)
                return $default;
            else
                return $text;
        } else
            return $default;
    }

    public static function initialize($lang = 'en'): ?string
    {
        if(isset($_GET['lang']))
            $lang = $_GET['lang'];
        else {
            $lang = TCookies::get('lang');
        }

        if(in_array($lang, ['cz']))
        {
            define('__LANGCLASS__', $lang);
            TCookies::set('lang', $lang, time()+3600, '/');
            return $lang;
        } else {
            TCookies::delete('lang');
        }

        return null;
    }
}

if(!function_exists('__'))
{
    function __($id, $default)
    {
        if($id != '')
        {
            if(defined('__LANGCLASS__'))
            {
                $classname = 'TLang'.strtoupper(__LANGCLASS__);
                if(class_exists($classname))
                {
                    $lang = $classname::getInstance();
                    return $lang->__($id, $default);
                } else
                    return $default;
            } else {
                $lang = TLang::getInstance();
                return $lang->__($id, $default);
            }
        } else
            return $default;
    }
}


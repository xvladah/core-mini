<?php

/**
 * Třída pro generování HTML stránky
 *
 * @name THtml
 * @version 1.0
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 */

declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

class THtml extends TElement
{
    const string ERROR_BAD_PARAMS	= '<!DOCTYPE html><html lang="en"><head><title>510 Bad Params</title></head><body><h1>Bad Params</h1><p>This POST or GET params are not supported.</p><hr><address>Apache/2.2.22 (Win32) mod_ssl/2.2.22 OpenSSL/0.9.8t PHP/5.3.11 Server at localhost Port 80</address></body></html>';
    const string ERROR_DATABASE	    = '<!DOCTYPE html><html lang="en"><head><title>500 Internal Database Error</title></head><body><h1>Internal Database Error</h1><p>Database value not found/allowed.</p><hr><address>Apache/2.2.22 (Win32) mod_ssl/2.2.22 OpenSSL/0.9.8t PHP/5.3.11 Server at localhost Port 80</address></body></html>';
    const string ERROR_FORBIDDEN	= '<!DOCTYPE html><html lang="en"><head><title>403 Forbidden</title></head><body><h1>Forbidden</h1><p>You don\'t have permission to access on this server folder.</p></body></html>';
    const string ERROR_NOT_FOUND	= '<!DOCTYPE html><html lang="en"><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL was not found on this server.</p><hr><address>Apache/2.2.22 (Win32) mod_ssl/2.2.22 OpenSSL/0.9.8t PHP/5.3.11 Server at localhost Port 80</address></body></html>';

    //const doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    const string doctype = '<!DOCTYPE html>';
    const string encoding = 'utf-8';
    const string br = '<br />';
    const string hr = '<hr />';
    const string nbsp = '&nbsp;';
    const string amp = '&amp;';
    const string thinsp = '&thinsp;';
    const string ndash = ' &ndash; ';
    const string mdash = ' &mdash; ';
    const int session_life_timeout = 3600;

    public array $_header = [];
    public THead $head;
    public TBody $body;

    public function __construct(string $title)
    {
        parent::__construct('html');
        $this->attributes->add('xmlns','http://www.w3.org/1999/xhtml');
        $this->attributes->add('xml:lang','cs');
        $this->attributes->add('lang','cs');
        $this->attributes->add('translate', 'no');

        if(session_status() === PHP_SESSION_NONE)
            session_start();

    //	setCookie(session_name(),session_id(),time()+self::session_life_timeout);

        $this->_header['X-UA-Compatible'] = 'IE=Edge';
        $this->_header['Expires'] = 'Mon, 1 Jan 2011 10:00:00 GMT';
        $this->_header['Cache-Control'] = 's-maxage=0,max-age=0,must-revalidate';
        $this->_header['Content-Type'] = 'text/html;charset='.self::encoding;

        $this->head = new THead($title);
        $this->childNodes->add($this->head);
        $this->head->addHtml(THead::Favicon('/favicon.ico'));
        $this->head->addHtml('<meta name="viewport" content="width=device-width,initial-scale=1" />');
        $this->head->addHtml('<meta name="google" content="notranslate" />');
//			$this->head->addHtml('<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />');

        $this->body = new TBody();
        $this->childNodes->add($this->body);
    }

    public static function h1($nodeValue, array $attrs = []) :string
    {
        return '<h1'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</h1>';
    }

    public static function h2($nodeValue, array $attrs = []) :string
    {
        return '<h2'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</h2>';
    }

    public static function h3($nodeValue, array $attrs = []) :string
    {
        return '<h3'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</h3>';
    }

    public static function p($nodeValue, array $attrs = []) :string
    {
        return '<p'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</p>';
    }

    public static function a($nodeValue, ?string $href, ?string $title, array $attrs = []) :string
    {
        $attrs['href'] = $href;

        if($title != '')
            $attrs['title'] = $title;

        return '<a'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</a>';
    }

    public static function img($file, ?string $alt, array $attrs = []) :string
    {
        $attrs['alt'] = $alt;

        if($alt != '')
            $attrs['title'] = $alt;

        return '<img src="' . $file . '"' . TAttributeList::getHtml($attrs) . ' />';
    }

    public static function div($nodeValue, ?string $class, array $attrs = []) :string
    {
        if($class != '')
            $attrs['class'] = $class;

        return '<div'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</div>';
    }

    public static function span($nodeValue, ?string $class, array $attrs = []) :string
    {
        if($class != '')
            $attrs['class'] = $class;

        return '<span'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</span>';
    }

    public static function b(string $nodeValue) :string
    {
        return '<b>'.$nodeValue.'</b>';
    }

    public static function i(string $nodeValue) :string
    {
        return '<i>'.$nodeValue.'</i>';
    }

    public static function u(string $nodeValue) :string
    {
        return '<u>'.$nodeValue.'</u>';
    }

    public static function big(string $nodeValue, array $attrs = []) :string
    {
        return '<big'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</big>';
    }

    public static function small($nodeValue, array $attrs = []) :string
    {
        return '<small'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</small>';
    }

    public static function tiny($nodeValue, array $attrs = []) :string
    {
        return '<span class="tin"'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</span>';
    }

    public static function strong($nodeValue, array $attrs = []) :string
    {
        return '<strong'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</strong>';
    }

    public static function em($nodeValue, array $attrs = []) :string
    {
        return '<em'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</em>';
    }

    public static function li($nodeValue, array $attrs = []) :string
    {
        return '<li'.TAttributeList::getHtml($attrs).'>'.$nodeValue.'</li>';
    }

    public function addCSS(string $file, ?string $media = '') :TElement
    {
        $this->head->addHtml(THead::CSS($file, $media));
        return $this;
    }

    public function addCSSCode(string $code, ?string $media = '') :TElement
    {
        $this->head->addHtml(THead::CSSCode($code, $media));
        return $this;
    }

    public function addJavaScript(string $file) :TElement
    {
        $this->head->addHtml(THead::JavaScript($file));
        return $this;
    }

    public function addJavaScriptCode(string $code) :TElement
    {
        $this->head->addHtml(THead::JavaScriptCode($code));
        return $this;
    }

    public function addJQueryCode(string $code) :TElement
    {
        $this->head->addHtml(THead::JQueryCode($code));
        return $this;
    }

    public function addEquiv(string $name, string $content) :TElement
    {
        $this->head->addHtml(THead::Equiv($name, $content));
        return $this;
    }

    public function addFavicon(string $icon) :TElement
    {
        $this->head->addHtml(THead::Favicon($icon));
        return $this;
    }

    public function addMeta(string $name, string $content) :TElement
    {
        $this->head->addHtml(THead::Meta($name, $content));
        return $this;
    }

    public function addHtml(?string $nodeValue) :TElement
    {
        $this->body->addHtml($nodeValue);
        return $this;
    }

    public function addHtmlBefore(?string $nodeValue) :TElement
    {
        $this->body->addHtmlBefore($nodeValue);
        return $this;
    }

    #[NoReturn] public static function errorForbidden(): void
    {
        header('HTTP/1.0 403 Forbidden');
        die(self::ERROR_FORBIDDEN);
    }

    #[NoReturn] public static function errorNotFound(): void
    {
        header('HTTP/1.0 404 Not Found');
        die(self::ERROR_NOT_FOUND);
    }

    #[NoReturn] public static function errorBadParams(): void
    {
        header('HTTP/1.0 403 Bad params');
        die(self::ERROR_BAD_PARAMS);
    }

    #[NoReturn] public static function errorDatabase(): void
    {
        header('HTTP/1.0 403 Database value not found/allowed');
        die(self::ERROR_DATABASE);
    }

    #[NoReturn] public static function errorChyba($text): void
    {
        die('<html lang="en"><head><title>Error</title><link type="text/css" rel="stylesheet" href="'.TStylesheet::Report().'" /><link type="text/css" rel="stylesheet" href="'.TStylesheet::GlyphIcons().'" /></head><body><div class="exception"><div class="logo"></div>'.$text.'</div></body></html>');
    }

    public function onLoad(string $javascript) :TElement
    {
        $this->body->onLoad($javascript);
        return $this;
    }

    public static function sanitizeAttr(?string $text) :?string
    {
        if ($text != '')
            return htmlspecialchars($text, ENT_QUOTES);
        else
            return $text;
    }

    public function html() :string
    {
        foreach($this->_header as $klic => $hodnota)
            Header($klic.': '.$hodnota);

        return self::doctype . parent::html();
    }

    public function innerHtml() :string
    {
        foreach($this->_header as $klic => $hodnota)
            Header($klic.':'.$hodnota);

        return $this->body->innerHtml();
    }
}
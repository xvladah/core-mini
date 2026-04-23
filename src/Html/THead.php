<?php

declare(strict_types=1);

class THead extends TElement
{
    public function __construct(string $title)
    {
        parent::__construct('head');
        $this->childNodes->add(new TElement('title', [], $title),'title');
    }

    public function setTitle(string $title): self
    {
        $this->childNodes->items['title']->nodeValue = $title;
        return $this;
    }

    public static function Title(string $title) :string
    {
        return '<title>'.$title.'</title>';
    }

    public static function Meta(string $name, string $content) :string
    {
        return '<meta name="'.$name.'" content="'.$content.'" />';
    }

    public static function Equiv(string $name, string $content) :string
    {
        return '<meta http-equiv="'.$name.'" content="'.$content.'" />';
    }

    public static function CSS(string $file, ?string $media = '') :string
    {
        $result = '<link type="text/css" rel="stylesheet" href="'. $file .'"';
        if($media != '')
            $result .= ' media="'. $media .'"';

        return $result . ' />';
    }

    public static function CSSCode(string $code, ?string $media = '') :string
    {
        $result = '<style type="text/css"';
        if($media != '')
            $result .= ' media="'. $media .'"';

        return $result . '>'. $code .'</style>';
    }

    public static function Favicon(string $icon) :string
    {
        return '<link rel="shortcut icon" href="'.$icon.'" type="image/x-icon" />'.
            '<link rel="icon" href="'.$icon.'" type="image/x-icon" />';
    }

    public static function JavaScript(string $file) :string
    {
        return '<script src="' . $file . '" type="text/javascript"></script>';
    }

    public static function JavaScriptCode(string $code, bool $debug = false) :string
    {
        if(!$debug)
            $code = preg_replace('/\s+/', ' ', $code);

        return '<script type="text/javascript">' . $code . '</script>';
    }

    public static function JQueryCode(string $code, bool $debug = false) :string
    {
        return self::JavaScriptCode($code, $debug);
    }
}



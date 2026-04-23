<?php

declare(strict_types=1);

class TCLIColors
{
    public const array FOREGROUND_COLORS = [
        'black' 	=> 30,
        'red'		=> 31,
        'green' 	=> 32,
        'yellow'	=> 33,
        'navy' 		=> 34,
        'lila'		=> 35,
        'cyan'  	=> 36,
        'gray'		=> 37,

        'pink'		=> 91,
        'lime'		=> 92,
        'beige'		=> 93,
        'blue'		=> 94,
        'purple'	=> 95,
        'aqua'		=> 96,
        'white'		=> 97
    ];

    public const array BACKGROUND_COLORS = [
        'black' 	=> 40,
        'red' 		=> 41,
        'green' 	=> 42,
        'yellow'	=> 43,
        'blue'  	=> 44,
        'magenta'	=> 45,
        'cyan' 		=> 46,
        'gray'  	=> 47
    ];

    public static function getColoredString(string $string, null|int|string $foreground_color = null, bool $bold = false, null|int|string $background_color = null): string
    {
        $colored_string = '';

        if($bold)
            $b = 1;
        else
            $b = 0;

        $num = null;
        if (isset(self::FOREGROUND_COLORS[$foreground_color]))
            $num = self::FOREGROUND_COLORS[$foreground_color];
        else
            if(is_integer($foreground_color))
                $num = $foreground_color;

        if($num !== null)
            $colored_string .= "\033[".$b.";" . $num . "m";

        $num = null;
        if (isset(self::BACKGROUND_COLORS[$background_color]))
            $num = self::BACKGROUND_COLORS[$background_color];
        else
            if(is_integer($background_color))
                $num = $background_color;

        if($num !== null)
            $colored_string .= "\033[" . $num . "m";

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }

    public static function getForegroundColors() :array
    {
        return array_keys(self::FOREGROUND_COLORS);
    }

    public static function getBackgroundColors() :array
    {
        return array_keys(self::BACKGROUND_COLORS);
    }
}



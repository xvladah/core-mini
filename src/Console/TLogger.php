<?php

/**
* @name TLogger
* @version 3.0
* @author vladimir.horky
* @copyright Vladimír Horký, 2020
*
* version 3.0
* TCLIColors is static
*
* version 2.0
* added new _call function support
*
* version 1.2
* added new functions for Exception
*
* version 1.1
* added new class TCLIColors
* added new functions getColoredString, getColoredStrings, isLinuxOS
*/

declare(strict_types=1);

class TLogger
{
    protected const int DEFAULT_DAYS 			    = 14;

    protected const string DEFAULT_TEXT_OK		    = 'OK';
    protected const string DEFAULT_TEXT_INFO	    = 'INFO';
    protected const string DEFAULT_TEXT_WARNING	    = 'WARNING';
    protected const string DEFAULT_TEXT_ERROR		= 'ERROR';
    protected const string DEFAULT_TEXT_EXCEPTION	= 'EXCEPTION';
    protected const string DEFAULT_TEXT_FAILED	    = 'FAILED';

    private array $params;
    private float $time_start 	= 0;

    private int $distance_info 	= 120;

    public ?string $filename 	= null;
    public string $content		= '';
    public ?string $logpath		= null;

    protected const array LINUX_OS = ['Linux','Unix','FreeBSD','OpenBSD','CentOS','Debian'];

    private bool $colorize;

    public function __construct(array $params, string $name, ?string $logpath = null)
    {
        $this->params = $params;

        if(isset($this->params['d']))
            switch($this->params['d'])
            {
                case '2' : error_reporting(E_ALL & ~E_PARSE & ~E_NOTICE & ~E_DEPRECATED); break;
                case '1' : error_reporting(E_ERROR | E_WARNING | E_PARSE); break;
                default	 : error_reporting(E_ERROR); break;
            }

        if($logpath !== null)
            $this->logpath = $logpath;

        if(isset($this->params['l']))
             $this->filename = Date('Y-m-d').'_'.$name.$this->params['g'].'.log';

        $this->colorize = !isset($_SERVER['SERVER_NAME']);
    }

    public function setColorize(bool $colorize) :TLogger
    {
        $this->colorize = $colorize;
        return $this;
    }

    public function setDistanceInfo(int $columns) :TLogger
    {
        $this->distance_info = $columns;
        return $this;
    }

    public function isLinuxOS() :bool
    {
        return (in_array(PHP_OS, self::LINUX_OS));
    }

    public function getColoredString(string $string, null|int|string $foreground_color = null, null|int|string $background_color = null) :string
    {
        if($this->colorize)
            return TCLIColors::getColoredString($string, $foreground_color, false, $background_color);
        else
            return $string;
    }

    public function getColoredText(string $string, ?string $prefix, ?string $note, ?string $postfix, ?int $distance = null, null|int|string $foreground_color = null, null|int|string $background_color = null) :string
    {
        if($this->colorize)
            $note = TCLIColors::getColoredString($note, $foreground_color, false, $background_color);

        $mb_length = strlen($string) - mb_strlen($string);
        return str_pad($string, $distance + $mb_length, ' ', STR_PAD_RIGHT) . $prefix . $note . $postfix;
    }

    protected function getColoredTextInfo(string $string, string $note = self::DEFAULT_TEXT_OK) :string
    {
        return $this->getColoredText($string, '[',$note,']', $this->distance_info, 'green', null);
    }

    protected function getColoredTextWarning($string, $note = self::DEFAULT_TEXT_WARNING) :string
    {
        return $this->getColoredText($string, '[',$note,']', $this->distance_info, 'yellow', null);
    }

    protected function getColoredTextError(string $string, string $note = self::DEFAULT_TEXT_ERROR) :string
    {
        return $this->getColoredText($string, '[',$note,']', $this->distance_info, 'red', null);
    }

    protected function getColoredTextFailed(string $string, string $note = self::DEFAULT_TEXT_FAILED) :string
    {
        return $this->getColoredText($string, '[',$note,']', $this->distance_info, 'red', null);
    }

    protected function getColoredNote(string $string, ?string $note = null, ?string $color = null) :string
    {
        return $this->getColoredText($string, '[',$note,']', $this->distance_info, $color, null);
    }

    public function get_delete_days() :int
    {
        if(!isset($this->params['l']))
            $days = self::DEFAULT_DAYS;
        else
            $days = $this->params['l'];

        return $days;
    }

    public function delete() :int
    {
        $count = 0;

        if($this->logpath !== null)
        {
            $tm = $this->get_delete_days() * 3600 * 24;
            if ($handle = opendir($this->logpath))
            {
                while(($filename = readdir($handle)) !== false)
                {
                    if(preg_match('/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}_data[0-9]{1,3}\.log$/i', $filename))
                    {
                        $matches = [];
                        preg_match('/[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}/', $filename, $matches, PREG_OFFSET_CAPTURE);
                        if(count($matches) == 1)
                        {
                            $time = strtotime($matches[0][0]);
                            if((strtotime(Date('Y-m-d')) - $time) > $tm)
                            {
                                $fullfilename = $this->logpath.'/'.$filename;
                                if(unlink($fullfilename))
                                {
                                    $this->info('Log file \''.$fullfilename.'\' deleted.');
                                    $count++;
                                } else
                                    $this->warning('Log file \''.$fullfilename.'\' not deleted!');
                            }
                        }
                    }
                }
            }

            closedir($handle);
        }

        return $count;
    }

    public function error(string $textmsg, int $level = 0, ?string $info = null): void
    {
        $this->write('Error: '.$textmsg, $level, $info);
    }

    public function errorERROR(string $textmsg, int $level = 0): void
    {
        $this->error($textmsg, $level, self::DEFAULT_TEXT_ERROR);
    }

    public function exception(mixed $msg): void
    {
        if($msg instanceof Exception)
            $msg = TErrors::formatException($msg);

        $this->write('Exception: '.$msg, -1);
    }

    public function exceptionEXCEPTION(mixed $msg): void
    {
        if($msg instanceof Exception)
            $msg = TErrors::formatException($msg);

        $this->error($msg, -1, self::DEFAULT_TEXT_EXCEPTION);
    }

    public function warning(string $textmsg, ?string $info = null): void
    {
        $this->write('Warning: '.$textmsg, 1, $info);
    }

    public function warningWARNING(string $textmsg): void
    {
        $this->warning($textmsg, self::DEFAULT_TEXT_WARNING);
    }

    public function info(string $textmsg, ?string $info = null): void
    {
        $this->write($textmsg, 2, $info);
    }

    public function infoOK(string $textmsg): void
    {
        $this->info($textmsg, self::DEFAULT_TEXT_OK);
    }

    public function echo(string $text, null|int|string $foreground_color = null, null|int|string $background_color = null): void
    {
        if(isset($this->params['d']))
        {
            if($this->params['d'] >= 2)
            {
                if($foreground_color !== null)
                    echo TCLIColors::getColoredString($text, $foreground_color, false, $background_color);
                else
                    echo $text;
            }
        }
    }

    public function echoValue(string $text, string $value, int $distance = 40, null|int|string $foreground_color = null, null|int|string $background_color = null): void
    {
        echo $this->getColoredText($text, null, $value, null, $distance, $foreground_color,  $background_color)."\n";
    }

    public function __call($function, $args)
    {
        @list($textmsg, $level) = $args;

        $substr = substr($function, 0, 4);
        if($substr == 'info')
        {
            $info = substr($function, 4);
            if($level === null)
                $level = 2;

            $this->write($textmsg, $level, $info);
        } else {
            $substr = substr($function, 0, 5);
            if($substr == 'error')
            {
                $info = substr($function, 5);
                if($level === null)
                    $level = 0;

                $this->write($textmsg, $level, $info);
            } else {
                $substr = substr($function, 0, 7);
                if($substr == 'warning')
                {
                    $info = substr($function, 7);
                    if($level === null)
                        $level = 1;

                    $this->write($textmsg, $level, $info);
                } else {
                    $substr = substr($function, 0, 9);
                    if($substr == 'exception')
                    {
                        $info = substr($function, 9);
                        if($level === null)
                            $level = -1;

                        $this->write($textmsg, $level, $info);
                    } else {
                        $substr = substr($function, 0, 3);
                        if($substr == 'msg')
                        {
                            $info = substr($function, 3);
                            if($level === null)
                                $level = 3;

                            $this->write($textmsg, $level, $info);
                        } else
                            trigger_error('Call to undefined method '.__CLASS__.'::'.$function.'()', E_USER_ERROR);
                    }
                }
            }
        }

        return null;
    }

    private function write(string $textmsg, int $level = 0, ?string $info = null): void
    {
        $t = microtime(true);
        $micro = sprintf("%06d",($t - floor($t)) * 1000000);

        $datetime = '['.Date('H:i:s.').substr($micro,0,3).']';
        $textmsg = $datetime.' '.$textmsg;

        if(isset($this->params['d']))
        {
            if($level <= $this->params['d'])
            {
                if($info !== null)
                {
                    echo match ($level) {
                        3 => $this->getColoredNote($textmsg, $info, 'blue') . "\n",
                        2 => $this->getColoredNote($textmsg, $info, 'lime') . "\n",
                        1 => $this->getColoredNote($textmsg, $info, 'yellow') . "\n",
                        default => $this->getColoredNote($textmsg, $info, 'red') . "\n",
                    };
                } else
                    echo $textmsg."\n";

                if($this->filename !== null)
                    $this->content .= $textmsg . "\n";
            }
        } else {
            if($level <= 0)
            {
                echo $this->getColoredTextError($textmsg)."\n";

                if($this->filename !== null)
                    $this->content .= $textmsg . "\n";
            }
        }

        if($level < 0)
        {
            $textmsg = $datetime.' Cron finished with exception! Error code '.$level.'.'."\n";
            echo $this->getColoredString($textmsg, 'red')."\n";

            if($this->filename !== null)
                $this->content .= $textmsg;

            $this->save();
            exit($level);
        }
    }

    public function save(): false|int
    {
        if($this->logpath !== null)
        {
            if($this->filename !== null && $this->content != '')
                return file_put_contents($this->logpath . '/'. $this->filename, $this->content . "\n", FILE_APPEND | LOCK_EX);
        }

        return false;
    }

    function gentime(): ?string
    {
        if($this->time_start == 0)
        {
            $this->time_start = microtime(true);
            return null;
        } else
            return number_format((microtime(true) - $this->time_start), 4);
    }

    function header(string $encoding = 'windows-1250', string $content_type = 'text/plain'): void
    {
        header('Content-Type:'.$content_type.';encoding='.$encoding);
    }

}
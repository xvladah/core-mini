<?php

declare(strict_types=1);

class TLangParser extends TLang
{
    const array SUPPORTED_EXTS = ['php','phtml','php3','php4','php5','php7'];

    protected string $delimiter = ';';

    /**
     * Funkce ukládá jazykové položky do CSV
     *
     * @filename string
     * @delimiter string
     * @return bool
     */
    public function SaveToCSV() :string
    {
        $content = '';
        foreach($this->items as $sysid => $data)
        {
            if($content != '')
                $content .= PHP_EOL;

            $content .= trim($sysid) . $this->delimiter . implode($this->delimiter, $data);
        }

        return chr(0xEF) . chr(0xBB) . chr(0xBF) . $content;
    }

    /**
     * Funkce generuje PHP soubor s jazykovými položkami
     *
     * @param string $mutation
     * @return int
     */
    public function SaveToPHP(string $mutation) :string
    {
        ksort($this->items);

        $vars = '';
        $last = '';
        foreach($this->items as $sysid => $text)
        {
            if($vars != '')
                $vars .= ',';

            $l = substr($sysid, 0, 3);
            if($l != $last)
            {
                $last = $l;
                $vars .= PHP_EOL;
            }

            $vars .= PHP_EOL."\t\t\t'".str_pad(trim($sysid)."'", 30, " ")."=>\t'".str_replace('\'','\\\'', trim($text))."'";
        }

        $content = <<<EOT
<?php
	require_once __DIR__ . '/../../vendor/core/Lang.php';

	class TLang$mutation extends TLang
	{
		public static \$instance;
		public array \$items = [$vars
	   	];
    }
EOT;
        return $content;
    }

    /**
     * Funkce načítá jazykové položky ze souboru CSV
     *
     * @param string $filename
     * @param int $column
     * @return int
     */
    public function LoadFromCSV(string $filename, int $langid) :int
    {
        if(file_exists($filename))
        {
            if(($handle = fopen($filename, "r")) !== false)
            {
                while(($data = fgetcsv($handle, 1000, $this->delimiter)) !== false)
                    $this->items[trim($data[0])] = trim($data[$langid]);

                fclose($handle);
            }
        } else
            throw new Exception('File "'.$filename.'" does not exist!');

        return count($this->items);
    }

    /**
     * Funkce pro zjištění přípony souboru
     *
     * @param string $filename
     * @return string
     */
    private static function Extension(string $filename) :string
    {
        $i = mb_strrpos($filename, '.');
        if($i > 0)
            return mb_substr($filename, $i + 1);
        else
            return '';
    }

    /**
     * Funkce prochází adresářovou strukturu a hledá požadované soubory (většinou PHP) dle přípony
     *
     * @param string $dir
     * @param array $results
     * @return array
     */
    private function getDirContents(string $dir, &$results = []) :array
    {
        $files = scandir($dir);

        foreach($files as $value)
        {
            $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
            if(!is_dir($path))
            {
                $ext = self::Extension($path);
                $basename = basename($path);

                if(in_array($ext, self::SUPPORTED_EXTS) && $ext != '' && !preg_match('/lang(\.)*\.php$/i', $basename))
                    $results[] = $path;
            } else
                if($value != '.' && $value != '..')
                    $this->getDirContents($path, $results);
        }

        return $results;
    }

    /**
     * Funkce načítá z PHP souborů resp. ze zadaného adresáře fráze pro překlad
     *
     * @param array $directories
     * @param boolean $debug
     * @return int
     */
    public function load(array $directories, bool $debug = false) :int
    {
        $files = [];
        foreach($directories as $dir)
            $this->getDirContents($dir, $files);

        foreach($files as $file)
        {
            echo $file.PHP_EOL;

            $end = 0;

            $content = file_get_contents($file);
            while(($start = mb_strpos($content, '__(\'', $end)) !== false)
            {
                $zal = $end;
                if(($end = mb_strpos($content, '\')', $start+4)) !== false)
                {
                    if($end > $start)
                    {
                        $substr = trim(mb_substr($content, $start, $end-$start));
                        if($substr != '' && !(strpos($substr, ')') !== false))
                        {
                            $end++;

                            $sentence = mb_strpos($substr, ',');
                            if($sentence !== false)
                            {
                                $sysid	= mb_strtolower(trim(mb_substr($substr, 4, $sentence-5)));
                                $data	= [0 => trim(ltrim(str_replace('\\\'','\'', mb_substr($substr, $sentence+2)), "\t\n\r\0\x0B\x27\x22"))];

                                if($debug)
                                    $data[0] .= '['.$file.']';

                                if($sysid != '')
                                {
                                    if(key_exists($sysid, $this->items))
                                    {
                                        if(!TStrings::StrCaseCmpi($this->items[$sysid][0], $data[0]))
                                        {
                                            $found = false;
                                            $i = 0;
                                            while(key_exists($sysid.'_#'.$i, $this->items))
                                            {
                                                if($this->items[$sysid.'_#'.$i][0] != $data[0])
                                                    $i++;
                                                else {
                                                    $found = ($this->items[$sysid.'_#'.$i][0] != '');
                                                    break;
                                                }
                                            }

                                            if(!$found)
                                                $this->items[$sysid.'_#'.$i] = $data;
                                        }
                                    } else
                                        $this->items[$sysid] = $data;
                                }
                            }
                        } else
                            $end = $zal + 4;
                    } else
                        $end = $zal + 4;
                } else
                    break;

            } // foreach
        }

        ksort($this->items);

        return count($this->items);
    }

    /**
     * Funkce doplňuje fráze do zadaného CSV souboru ze souborů (většinou PHP), které leží ve specifikovaných cestách
     *
     * @param string $filename
     * @param string $delimiter
     * @param number $column
     * @return int
     */
    public function AppendToCSV(string $filename) :string
    {
        $items = [];

        if(file_exists($filename))
        {
            if(($handle = fopen($filename, "r")) !== false)
            {
                while(($data = fgetcsv($handle, 1024, $this->delimiter)) !== false)
                {
                    $key = $data[0];
                    array_shift($data);
                    $items[$key] = $data;
                }

                fclose($handle);
            }
        } else
            throw new Exception('File "'.$filename.'" does not exist!');

        foreach($this->items as $langid => $data)
        {
            if(!key_exists($langid, $items))
                $items[$langid] = $data;
        }

        ksort($items);

        $content = '';
        foreach($items as $langid => $subitems)
        {
            if($content != '')
                $content .= PHP_EOL;

            $content .= trim($langid);

            foreach($subitems as $item)
                $content .= $this->delimiter . trim($item);
        }

        return $content;
    }

    /**
     * Funkce, ktera nacte fraze ze slovniku
     *
     * @param string $filename
     * @param string $delimiter
     * @throws Exception
     * @return int
     */

    public function LoadPhrases(string $filename) :int
    {
        $result = 0;
        $items 	= [];

        if(file_exists($filename))
        {
            if(($handle = fopen($filename, "r")) !== false)
            {
                while(($data = fgetcsv($handle, 1024, $this->delimiter)) !== false)
                {
                    $key = $data[0];
                    array_shift($data);
                    $items[$key] = $data;
                }

                fclose($handle);
            }
        } else
            throw new Exception('Dictionary file "'.$filename.'" does not exist!');

        foreach($items as $sysid => $data)
        {
            if(key_exists($sysid, $this->items))
            {
                foreach($data as $langid => $text)
                {
                    if($text != '')
                    {
                        if($langid < 1)
                            continue;

                        if($this->items[$sysid][$langid] == '')
                        {
                            $this->items[$sysid][$langid] = $text;
                            $result++;
                        }
                    }
                }
            }
        }

        return $result;
    }
}


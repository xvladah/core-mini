<?php

/***
 * @name UnZip
 * @category UnZip
 * @version 1.0
 * @author Vladimir Horky <vladimir.horky@tedom.com>
 * @copyright 2023 TEDOM a.s.
 * @licence Vladimir Horky, TEDOM a.s.
 */

class UnZip
{
    public ?string $archive;
    public $file;

    public function __construct(string $archive)
    {
        $this->archive = $archive;
        $this->file = zip_open($this->archive);
    }

    public function Next()
    {
        return zip_read($this->file);
    }

    public function getFileName($item): false|string
    {
        return zip_entry_name($item);
    }

    public function getFileSize($item): false|int
    {
        return zip_entry_filesize($item);
    }

    // funkce, ktera vraci data podle nastavene polozky
    public function getContent($item): false|string
    {
        if (zip_entry_open($this->file, $item, "r"))
        {
            $buffer = zip_entry_read($item, zip_entry_filesize($item));
            zip_entry_close($item);
            return $buffer;
        } else
            return '';
    }

    // funkce, ktera vrati data podle nazvu souboru
    public function getOutput($filename): false|string
    {
        while($item = $this->Next())
        {
             if(strcasecmp($this->getFileName($item), $filename) == 0)
                return $this->getContent($item);
        }

        return '';
    }

    public function Close(): void
    {
        zip_close($this->file);
    }

    public function __destruct()
    {
        if($this->file)
            $this->Close();
    }

}

<?php

/**
 *  Zip for file compress and uncompress
 *
 * @name Zip
 * @category Zip
 * @version 2.0
 * @author Vladimir Horky <vladimir.horky@tedom.com>
 * @copyright 2023 TEDOM a.s.
 * @licence Vladimir Horky, TEDOM a.s.
 *
 *  version 2.0
 *  - changed addFile methods, removed basename for filename
 */

class Zip
{
    public array $datasec      = [];  //     * Array to store compressed data
    public array $ctrl_dir     = [];  //     * Central directory
    public string $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00"; // End of central directory record
    public int $old_offset   = 0;  // Last offset position
    public ?string $archive;

    public function __construct(string $filename)
    {
        $this->archive = $filename;
    }

    /**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     * @param  integer $unixtime the current Unix timestamp
     * @return integer  the current date in a four byte DOS format
     * @access private
     */
    public function unix2DosTime(int $unixtime = 0): int
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        }
        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
            ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    }

    /**
     * @param  string $data file contents
     * @param  string $name name of the file in the archive (may contains the path)
     * @param  integer $time the current timestamp
     */
    public function addFile(string $data, string $name, int $time = 0): void
    {
        $name     = str_replace('\\', '/', $name);

        $dtime    = dechex($this->unix2DosTime($time));
        $hexdtime = '\x' . $dtime[6] . $dtime[7]
            . '\x' . $dtime[4] . $dtime[5]
            . '\x' . $dtime[2] . $dtime[3]
            . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');

        $fr   = "\x50\x4b\x03\x04";
        $fr   .= "\x14\x00";            // ver needed to extract
        $fr   .= "\x00\x00";            // gen purpose bit flag
        $fr   .= "\x08\x00";            // compression method
        $fr   .= $hexdtime;             // last mod time and date

        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2); // fix crc bug
        $c_len   = strlen($zdata);
        $fr      .= pack('V', $crc);             // crc32
        $fr      .= pack('V', $c_len);           // compressed filesize
        $fr      .= pack('V', $unc_len);         // uncompressed filesize
        $fr      .= pack('v', strlen($name));    // length of filename
        $fr      .= pack('v', 0);                // extra field length
        $fr      .= $name;

        $fr .= $zdata;

        $fr .= pack('V', $crc);                 // crc32
        $fr .= pack('V', $c_len);               // compressed filesize
        $fr .= pack('V', $unc_len);             // uncompressed filesize

        $this -> datasec[] = $fr;

        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";                // version made by
        $cdrec .= "\x14\x00";                // version needed to extract
        $cdrec .= "\x00\x00";                // gen purpose bit flag
        $cdrec .= "\x08\x00";                // compression method
        $cdrec .= $hexdtime;                 // last mod time & date
        $cdrec .= pack('V', $crc);           // crc32
        $cdrec .= pack('V', $c_len);         // compressed filesize
        $cdrec .= pack('V', $unc_len);       // uncompressed filesize
        $cdrec .= pack('v', strlen($name) ); // length of filename
        $cdrec .= pack('v', 0 );             // extra field length
        $cdrec .= pack('v', 0 );             // file comment length
        $cdrec .= pack('v', 0 );             // disk number start
        $cdrec .= pack('v', 0 );             // internal file attributes
        $cdrec .= pack('V', 32 );            // external file attributes - 'archive' bit set

        $cdrec .= pack('V', $this -> old_offset ); // relative offset of local header
        $this -> old_offset += strlen($fr);

        $cdrec .= $name;

        $this -> ctrl_dir[] = $cdrec;
    }

    public function file(): string
    {
        $data    = implode('', $this -> datasec);
        $ctrldir = implode('', $this -> ctrl_dir);

        return
            $data .
            $ctrldir .
            $this -> eof_ctrl_dir .
            pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries "on this disk"
            pack('v', sizeof($this -> ctrl_dir)) .  // total # of entries overall
            pack('V', strlen($ctrldir)) .           // size of central dir
            pack('V', strlen($data)) .              // offset to start of central dir
            "\x00\x00";                             // .zip file comment length
    }

    public function addFiles(array $files): void
    {
        foreach($files as $file)
        {
            if (is_file($file)) //directory check
            {
                $data = implode('', file($file));
                $this->addFile($data, $file);
            }
        }
    }

    public function save(): void
    {
        $f = fopen($this->archive, 'wb');
        fwrite($f, $this->file(), strlen($this->file()));
        fclose($f);
    }

    public function getOutput(): void
    {
        Header('Pragma: public');
        Header('Expires: 0');
        Header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        Header('Cache-Control: private', false);
        Header('Content-Transfer-Encoding: binary');
        Header('Content-Type: application/octet-stream');
        Header('Content-Length: '.strlen($this->file()));
        Header('Content-Disposition: inline; filename="'.basename($this->archive).'"');

        echo $this->file();
    }
}
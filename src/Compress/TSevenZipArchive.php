<?php

/**
 * Třída pro kompresi a dekompresi 7zip
 *
 * @name TSevenZipArchive
 * @version 2.0
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2023 TEDOM
 */


class TSevenZipArchive
{
    // formats for compression and decompression
    const string FORMAT_7ZIP  = '7z';
    const string FORMAT_XZ    = 'xz';
    const string FORMAT_BZIP2 = 'bzip2';
    const string FORMAT_GZIP  = 'gzip';
    const string FORMAT_TAR   = 'tar';
    const string FORMAT_ZIP   = 'zip';
    const string FORMAT_WIM   = 'wim';

    // formats only for decompression
    const string FORMAT_ARJ      = 'arj';
    const string FORMAT_CAB      = 'cab';
    const string FORMAT_CHM      = 'chm';
    const string FORMAT_CPIO     = 'cpio';
    const string FORMAT_CRAM_FS  = 'cramfs';
    const string FORMAT_DEB      = 'deb';
    const string FORMAT_DMG      = 'dmg';
    const string FORMAT_FAT      = 'fat';
    const string FORMAT_HFS      = 'hfs';
    const string FORMAT_ISO      = 'iso';
    const string FORMAT_LZH      = 'lhz';
    const string FORMAT_LZMA     = 'lzma';
    const string FORMAT_MBR      = 'mbr';
    const string FORMAT_MSI      = 'msi';
    const string FORMAT_NSIS     = 'nsis';
    const string FORMAT_NTFS     = 'ntfs';
    const string FORMAT_RAR      = 'rar';
    const string FORMAT_RPM      = 'rpm';
    const string FORMAT_SQASH_FS = 'sqashfs';
    const string FORMAT_UDF      = 'udf';
    const string FORMAT_VHD      = 'vhd';
    const string FORMAT_XAR      = 'xar';
    const string FORMAT_Z        = 'z';

    protected ?string $binary;
    protected string $format = self::FORMAT_7ZIP;
    protected array $meta = [];
    protected bool $recursive = false;
    protected ?string $password;
    protected ?bool $password_names = false;
    protected bool $debug = false;

    public function __construct(string $executablePath, string $archiveFormat = self::FORMAT_7ZIP)
    {
        $this->binary = $executablePath;
        $this->format = $archiveFormat;
    }

    public function checkBinary(): bool
    {
        if(!function_exists('exec'))
            return false;

        $rc = null;
        $output = [];

        $cmd = '"'.escapeshellcmd($this->binary).'"';

        exec($cmd.' 2>&1', $output, $rc);

        $this->debug && error_log(__METHOD__ . ' rc: ' . $rc);
        $this->debug && error_log(__METHOD__ . ' Output: ' . join("\n", $output) . "\n");

        if($rc)
        {
            trigger_error("\"$cmd\" call failed with return code: $rc", E_USER_ERROR);
            return false;
        } else
            return true;
    }

    public function getDebug(): bool
    {
        return $this->debug;
    }

    public function setDebug(bool $debug = true): void
    {
        $this->debug = $debug;
    }

    public function setBinary(string $path): void
    {
        $this->binary = $path;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function setPassword(?string $password, bool $password_names = true): void
    {
        $this->password = $password;
        $this->password_names = $password_names;
    }

    public function setRecursive(bool $recursive = true): void
    {
		$this->recursive = $recursive;
    }
    
    public function compress(string $destArchiveFile, $srcFiles): bool
    {
        $src = '';
        if(is_array($srcFiles))
        {
            foreach($srcFiles as $srcFile)
            {
                if($src != '')
                    $src .= ' ';

                $src .= '"'.escapeshellarg($srcFile).'"';
            }
        } else
            $src = '"'.escapeshellarg($srcFiles).'"';

        $cmd = '"' . escapeshellcmd($this->binary) . '"'
               . ' a'
               . ' -t'.$this->format
               . $this->getPasswordParam()
               . ' ' . escapeshellarg($destArchiveFile)
               . $this->getRecursive()
			   . ' ' . $src;

        $output = [];
        $rc = null;

        exec($cmd.' 2>&1', $output, $rc);

        $this->debug && error_log(__METHOD__ . ' rc: ' . $rc);
        $this->debug && error_log(__METHOD__ . ' Output: ' . join("\n", $output) . "\n");

        if($rc)
        {
            trigger_error("\"$cmd\" call failed with return code: $rc\n".print_r($output, true), E_USER_ERROR);
            return false;
        } else {
            if(str_contains(implode('',$output), 'Everything is Ok'))
                return true;
            else {
                trigger_error("\"$cmd\" unknown status: $rc\n".print_r($output, true), E_USER_ERROR);
                return false;
            }
        }
    }

    public function decompress(string $srcArchiveFile, string $destPath, $files = ''): bool
    {
        $filesFilter = '';

        if(is_array($files))
        {
            foreach($files as $file)
            {
                if($filesFilter != '')
                    $filesFilter .= ' ';

                $filesFilter .= '"'.escapeshellarg($file).'"';
            }
        } else {
            $filesFilter = '"'.escapeshellarg($files).'"';
        }

        $cmd = '"' . escapeshellcmd($this->binary) . '"'
               . ' e'
               . ' -y'
               . $this->getPasswordParam()
               . ' ' . escapeshellarg($srcArchiveFile)
               . ' -o' . escapeshellarg($destPath)
               . ' '.$filesFilter;

        $output = [];
        $rc = null;

        exec($cmd.' 2>&1', $output, $rc);

        $this->debug && error_log(__METHOD__ . ' rc: ' . $rc);
        $this->debug && error_log(__METHOD__ . ' Output: ' . join("\n", $output) . "\n");

        if($rc)
        {
            trigger_error("\"$cmd\" call failed with return code: $rc\n".print_r($output, true), E_USER_ERROR);
            return false;
        } else {
            if(str_contains(implode('',$output), 'Everything is Ok'))
                return true;
            else {
                trigger_error("\"$cmd\" unknown status: $rc\n".print_r($output, true), E_USER_ERROR);
                return false;
            }
        }
    }

    /**
     * @throws TSevenZipArchive
     * @throws Exception
     */
    public function list(string $srcArchiveFile) : array
    {
        $cmd = '"' . escapeshellcmd($this->binary) . '"' .
               ' l' .
               $this->getPasswordParam() .
               ' ' . escapeshellarg($srcArchiveFile);

        $this->debug && error_log(__METHOD__ . ' Command: ' . $cmd);

        $rc = null;
        $output = [];

        exec($cmd, $output, $rc);

        $this->debug && error_log(__METHOD__ . ' rc: ' . $rc);
        if($rc)
        {
            $this->debug && error_log(__METHOD__ . ' output: ' . join("\n", $output));
            throw new ESevenZipArchive("\"$cmd\" call failed with return code: $rc");
        }

        $errors = [];
        $meta_started = false;
        $meta = [];
        $entries_started = false;
        $entries_field_widths = [];
        $entries = [];

        foreach($output as $line)
        {
            if(!$meta_started)
            {
                if ($line === '--')
                    $meta_started = true;
                elseif (preg_match('/^Error:\s*(.+)/', $line, $matches))
                    $errors []= $matches[1];

                continue;
            }

            # Read meta data until entries start
            if(!$entries_started)
            {
                if (preg_match('/^(Type|Method|Solid|Blocks|Physical Size|Headers Size) = (.*)/', $line, $matches))
                    $meta[$matches[1]] = $matches[2];
                elseif (preg_match('/^
					(-+\s+)	# DateTime
					(-+\s+)	# Attr
					(-+\s+)	# Size
					(-+\s+)	# Compressed
					(-+)	# Name
				$/x', $line, $matches)) {
                    $entries_started = true;
                    $entries_field_widths['DateTime']		= strlen($matches[1]);
                    $entries_field_widths['Attr']			= strlen($matches[2]);
                    $entries_field_widths['Size']			= strlen($matches[3]);
                    $entries_field_widths['Compressed']	    = strlen($matches[4]);
                    $entries_field_widths['Name']			= null;
                }
                continue;
            }

            # Read entries until end
            if(str_starts_with($line, '-'))
                break;

            $x = 0;
            $entry = [];
            foreach($entries_field_widths as $k => $w)
            {
                $entry[$k] = trim($w ? substr($line, $x, $w) : substr($line, $x));
                $x += $w;
            }

            $entries []= $entry;
        }

        if($errors)
            throw new ESevenZipArchive("Error(s) listing archive contents:\n" . join("\n", $errors));

        $this->meta = $meta;
        return $entries;
    }

    public function verify(string $srcArchiveFile): bool
    {
        $cmd = '"' . escapeshellcmd($this->binary) . '"'
                 . ' t'
                 . $this->getPasswordParam()
                 . ' '.escapeshellarg($srcArchiveFile);

        $rc = null;
        $output = [];

        exec($cmd, $output, $rc);

        $this->debug && error_log(__METHOD__ . ' rc: ' . $rc);
        $this->debug && error_log(__METHOD__ . ' Output: ' . join("\n", $output) . "\n");

        if($rc)
        {
            trigger_error("\"$cmd\" call failed with return code: $rc\n".print_r($output, true), E_USER_ERROR);
            return false;
        } else {
            if(str_contains(implode('',$output), 'Everything is Ok'))
                return true;
            else {
                trigger_error("\"$cmd\" unknown status: $rc\n".print_r($output, true), E_USER_ERROR);
                return false;
            }
        }
    }
    
    protected function getRecursive(): string
    {
    	if($this->recursive)
    		return ' -r';
    	else
    		return '';
    }

    protected function getPasswordParam(): string
    {
        $pwd = '';
        if(!empty($this->password))
        {
            $pwd = ' -p' . escapeshellarg($this->password);

            if($this->password_names)
            	$pwd .= ' -mhe=on';
        }
        
        return $pwd;
    }
}
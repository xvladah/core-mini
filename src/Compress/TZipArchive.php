<?php
/**
 * Copyright (c) TEDOM a.s.
 * @name TZipArchive
 * @author Vladimír Horký
 * @version 1.2
 * @copyright TEDOM a.s.
 *
 * version 1.2
 * changed function getSizeIndex
 * changes function getFileSize
 *
 * version 1.1
 * added property $archiveFile
 * changed function getOutput()
 *
 */

class TZipArchive extends ZipArchive
{
    public string $archiveFile;

    private int $indexIterator = -1;

    public function createArchive(string $file): bool|int
    {
        $this->archiveFile = $file;
        return $this->open($file, ZipArchive::CREATE);
    }

    public function openArchive(string $file): bool|int
    {
        $this->archiveFile = $file;
        return $this->open($file);
    }

    public function addFiles(array $files): bool
    {
        $result = true;

        foreach($files as $file)
        {
            $result &= $this->addFile($file);
        }

        return $result;
    }

    public function getSizeIndex(int $index): int
    {
        return $this->statIndex($index)['size'];
    }

    public function getFileSize(string|int $filename): int
    {
        if(is_int($filename))
            return $this->statIndex($filename)['size'];
        else
            return $this->statName($filename)['size'];
    }

    public function getFileName(string $filename): string
    {
        return $this->statName($filename)['name'];
    }

    public function count(): int
    {
        return $this->numFiles;
    }

    public function reset(): void
    {
        $this->indexIterator = -1;
    }

    public function next(): string|bool
    {
        if($this->numFiles > 0)
        {
            if(++$this->indexIterator < $this->numFiles - 1)
            {
                return $this->getFromIndex($this->indexIterator);
            } else
                return false;
        } else
            return false;
    }

    /**
     * @throws Exception
     */
    public function getOutput(bool $unlinkArchiveFile = true): false|int
    {
        if($this->archiveFile != '' && is_file($this->archiveFile) && is_readable($this->archiveFile))
        {
            if(str_ends_with($this->archiveFile, '.zip'))
            {
                $mimeType = 'application/zip';
            } else {
                $mimeType = 'application/octet-stream';
            }

            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            {
                header('Cache-Control: max-age=120');
                header('Pragma: public');
            } else {
                header('Cache-Control: private, max-age=120, must-revalidate');
                header('Pragma: no-cache');
            }

            $safeFilename = rawurlencode(basename($this->archiveFile));

            header('Content-Transfer-Encoding: binary');
            header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
            header('Accept-Ranges: bytes');
            header('Content-Length: ' . filesize($this->archiveFile));

            $result = readfile($this->archiveFile);
            if ($result === false) {
                throw new Exception('Failed to read archive file "'.$this->archiveFile.'"!', -10001);
            }

            if($unlinkArchiveFile)
            {
                if (!unlink($this->archiveFile)) {
                    error_log('Failed to delete temporary file: ' . $this->archiveFile);
                }
            }
        } else {
            throw new Exception('Archive file "' . $this->archiveFile . '" does not exist or is not readable!', -10000);
        }

        return $result;
    }
}
<?php

declare(strict_types=1);

abstract class TSysFileNode
{
    protected ?string $name  	 = null;
    protected ?string $directory = null;

    /**
     * @throws ESysFile
     */
    public function __construct(?string $fullname)
    {
        $this->setFullName($fullname);
    }

    public function getDirectory(): ?string
    {
        return $this->directory;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name) :TSysFileNode
    {
        $this->name = $name;
        return $this;
    }

    public static function BaseName(?string $filename): ?string
    {
        if($filename != '')
        {
            $filename = self::FileName($filename);

            $i = mb_strrpos($filename, '.');
            if($i > 0)
                return mb_substr($filename, 0, $i);
            else
                return '';
        } else
            return $filename;
    }

    public function getBaseName(): ?string
    {
        return self::BaseName($this->name);
    }

    public function setFullName(string $fullname) :TSysFileNode
    {
        $this->directory = self::DirectoryPath($fullname);
        $this->name	 	 = self::FileName($fullname);

        return $this;
    }

    public function getFullName(): string
    {
        return $this->directory . $this->name;
    }

    public static function DirectoryPath(?string $file): string
    {
        $filename 	= self::FileName($file);
        return mb_substr($file, 0, mb_strlen($file) - mb_strlen($filename));
    }

    public static function FileNameShorten(?string $filename, int $length = 50, string $sequence = '... '): string
    {
        if($filename != '')
        {
            $i = mb_strrpos($filename, '.');
            $sequence_length = strlen($sequence);
            if($i > 0)
            {
                $extension = mb_substr($filename, $i + 1);
                $extension_length = mb_strlen($extension);

                $basename =  mb_substr($filename, 0, $i);

                if($length < mb_strlen($filename))
                {
                    $length -= ($extension_length + $sequence_length + 1);
                    $result = mb_substr($basename, 0, $length).$sequence.'.'.$extension;
                } else
                    $result = $filename;

            } else {
                if($length < mb_strlen($filename))
                {
                    $length -= ($sequence_length + 1);
                    $result = mb_substr($filename, 0, $length).$sequence.'.';
                } else
                    $result = $filename;
            }
        } else
            $result = $filename;

        return $result;
    }

    public function getDirectoryPath(string $file): string
    {
        return self::DirectoryPath($file);
    }

    public static function FileName(string $file): false|string|null
    {
        if($file != '')
        {
            $paths1 = explode('/', $file);
            $paths2 = explode('\\', $file);

            $c1 = count($paths1);
            $c2 = count($paths2);

            if($c2 >= $c1)
            {
                if($c2 > 0)
                    $filename = end($paths2);
                else
                    $filename = $file;
            } else {
                if($c1 > 0)
                    $filename = end($paths1);
                else
                    $filename = $file;
            }
        } else
            $filename = $file;

        return $filename;
    }

    public function getFileName(string $file): false|string|null
    {
        return self::FileName($file);
    }

    public static function Extension(?string $filename): ?string
    {
        if($filename != '')
        {
            $i = mb_strrpos($filename, '.');
            if($i > 0)
                $extension = mb_substr($filename, $i + 1);
            else
                $extension = '';
        } else
            $extension = $filename;

        return $extension;
    }

    public function getExtension(): ?string
    {
        return self::Extension($this->name);
    }

    public function rename(?string &$newName, bool $sameDirectory = true) :bool
    {
        if($sameDirectory)
            $newName = $this->getDirectory() . self::FileName($newName);

        if(rename($this->getFullName(), $newName))
        {
            $this->setFullName($newName);
            return true;
        } else
            return false;
    }

    public function isReadable() :bool
    {
        return is_readable($this->getFullName());
    }

    public abstract function delete() :bool;

    public abstract function copy(string $dest) :bool;

    public abstract function exists() :bool;

    public function chmode(int $mode) :bool
    {
        return chmod($this->directory, $mode);
    }

    public function chown($user) :bool
    {
        return chown($this->directory, $user);
    }

    public static function file_exists(?string $filename) :bool
    {
        if($filename != '')
            $result = file_exists($filename);
        else
            $result = false;

        return $result;
    }

    public function __toString()
    {
        return $this->getFullName();
    }
}

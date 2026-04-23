<?php

/**
 * Třída pro práci se soubory
 *
 * @name TSysFileNode
 * @version 1.3
 * @author vladimir.horky
 * @copyright Vladimír, Horký, 2021.
 *
 * version 1.3
 * added function genNextFileName
 *
 * @note
 * version 1.2
 * added function FileNameShorten
 * 
 * version 1.1
 * added function DirectoryName
 * added function getDirectoryName
 * added function FileName
 * added function getFileName
 */

declare(strict_types=1);

class TSysFile extends TSysFileNode
{
	public function exists() :bool
	{
		return file_exists($this->getFullName());
	}

	public function delete() :bool
	{
		if($this->isReadable())
		{
			$filename = $this->getFullName();
			@unlink($filename);

			return !file_exists($filename);
		} else
			return false;
	}

	public function copy(string $dest) :bool
	{
		$dest = rtrim($dest, '/');

		if(is_dir($dest))
			$dest .= '/'.$this->name;

		$filename = $this->getFullName();
		copy($filename, $dest);

		return file_exists($dest);
	}

	public function load() :string
	{
		return file_get_contents($this->directory . $this->name);
	}

	public function save(string $data) :bool
	{
		$filename = $this->directory . $this->name;
		file_put_contents($filename, $data);

		return file_exists($filename);
	}

	public function append(string $data) :bool
	{
		$filename = $this->directory . $this->name;
		file_put_contents($filename, $data, FILE_APPEND);

		return file_exists($filename);
	}

	public static function saveAs(string $fullname, string $data) :bool
	{
		file_put_contents($fullname, $data);
		return file_exists($fullname);
	}

	public static function SHA1(string $filename) :string
	{
		return sha1(file_get_contents($filename));
	}

	public function getSHA1(): string
    {
		return self::SHA1($this->getFullName());
	}

	public function getSize() :int
	{
		return filesize($this->getFullName());
	}

	public function getFormatSize() :string
	{
		return self::FormatSize($this->getSize());
	}

	public static function FormatSize(?int $bytes) :string
	{
		if($bytes !== null)
		{
			$label = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
			$c = count($label) - 1;
	
			for($i = 0; $bytes >= 1024 && $i < $c; $bytes /= 1024, $i++);
	
			if($i > 0)
				return number_format((round($bytes, 2)), 2, '.', ' '). ' ' . $label[$i];
			else
				return number_format($bytes, 0, '.', ' '). ' ' . $label[0];
		} else 
			return '';
	}
	
	public static function genNextFileName(?string $file) :string
	{
		$basename 	= self::BaseName($file);
		$extension 	= self::Extension($file);
		$path 		= self::DirectoryPath($file);
		
		$i = 1;
		
		$result = $path . $basename . '('.$i.').'.$extension;
		while(file_exists($result))
        {
            $result = $path . $basename . '(' . ++$i . ').' . $extension;
        }

		return $result;
	}
}
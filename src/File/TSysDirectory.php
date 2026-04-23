<?php

/**
 * Třída pro práci s adresáři
 *
 * @name TSysDirectory
 * @version 1.0
 * @author vladimir.horky
 * @copyright Vladimír, Horký, 2018.
 */

declare(strict_types=1);

class TSysDirectory extends TSysFileNode
{
	public function exists() :bool
	{
		return is_dir($this->getFullName());
	}

	public function createIfNotExists(int|string $rights = 0777) :bool
	{
		if(!$this->exists())
		{
			$path = $this->getFullName();
			$default_umask = umask(0);
			$result = mkdir($path, $rights, true);
			umask($default_umask);
			return $result;
		} else
			return true;
	}

	protected static function doDelete(string $dir) :bool
	{
		$dir = rtrim($dir, '/');

		if (!file_exists($dir))
			return true;

		chmod($dir, 0777);
		if (!is_dir($dir) || is_link($dir))
			return unlink($dir);

		foreach(scandir($dir) as $item)
		{
			if($item != '.' && $item != '..')
			{
				if (!self::doDelete($dir.'/'.$item))
					return false;
			}
		}

		return rmdir($dir);
	}

	public function delete() :bool
	{
		return self::doDelete($this->getFullName());
	}

	public static function deleteDir(string $dir) :bool
	{
		return self::doDelete($dir);
	}

	public function clear() :bool
	{
		$dir = rtrim($this->getDirectory(), '/');
		if (!is_dir($dir))
			return true;
		else
			if(!is_readable($dir))
				return false;

		foreach(scandir($dir) as $item)
		{
			if($item != '.' && $item != '..')
			{
				if (!self::doDelete($dir.'/'.$item))
					return false;
			}
		}

		return true;
	}

	public function copy(string $dest) :bool
	{
		return self::recurseCopy($this->getFullName(), $dest);
	}

	private static function recurseCopy(string $fromDir, string $toDir) :bool
	{
		$src = rtrim($fromDir, '/');
		$dst = rtrim($toDir, '/');

		$dir = opendir($src);
		if(!is_dir($dst))
			mkdir($dst, 0777, true);
		else
			chmod($dst, 0777);

		while(false !== ($file = readdir($dir)))
		{
			if($file != '.' && $file != '..')
			{
				if(is_dir($src.'/'.$file))
					self::recurseCopy($src.'/'.$file, $dst.'/'.$file);
				else
					copy($src.'/'.$file, $dst.'/'.$file);
			}
		}

		closedir($dir);
		return true;
	}

    /**
     * @throws ESysFile
     */
    public function scanDir(bool $hideThisParent = true): false|array
    {
		if(!$this->isReadable())
			return [];

		$content = scandir($this->getFullName());
		if($hideThisParent)
			$content = array_diff($content, array('.', '..'));

		foreach($content as &$value)
		{
			if(is_dir($this->getFullName().'/'.$value))
				$value = new TSysDirectory($this->getFullName().'/'.$value);
			else
				$value = new TSysFile($this->getFullName().'/'.$value);
		}

		return $content;
	}

    /**
     * @throws ESysFile
     */
    public function scanDirRecursive(bool $hideThisParent = true): array
    {
		if(!$this->isReadable())
			return [];

		$result = [];
		$subItems = $this->scanDir($hideThisParent);
		foreach($subItems as $subItem)
		{
			$result[] = $subItem;
			if($subItem instanceof TSysDirectory)
				$result = array_merge($result, $subItem->scanDirRecursive($hideThisParent));
		}

		return $result;
	}
}
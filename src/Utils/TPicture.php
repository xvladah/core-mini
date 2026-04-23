<?php

/**
 * TPicture for pictures manipulation
 *
 * @package Utils
 * @category TPicture
 * @version 1.0
 * @author Vladimir Horky <vladimir.horky@tedom.com>
 * @copyright 2017 TEDOM a.s.
 * @licence Vladimir Horky, TEDOM a.s.
 */

declare(strict_types=1);

class TPicture
{
	private string $filename	= '';
	public 	int $width		    = 0;
	public 	int $height		    = 0;
	public 	string $mime		= '';
	public 	string $extension	= '';
    /**
     * @var int|mixed
     */
    private mixed $type;

	public function load(string $filename) :static
	{
		$this->filename = $filename;
		list($this->width, $this->height, $this->type) = getimagesize($this->filename);
		$this->ext = $this->getExt($this->filename);
		$this->mime = $this->getMime($this->filename);

		return $this;
	}

	public static function getExt(string $filename) :string
	{
		$f = explode('.', $filename);
		$ext = $f[count($f)-1];
		if(in_array($ext, array('pjpeg','jpeg','jpg','png','x-png','gif')))
			return $ext;
		else
			return 'jpg';
	}

	public static function getMime(string $filename) :string
	{
		$mimes = array('pjpeg'=>'image/pjpeg', 'jpeg'=>'image/jpeg', 'jpg'=>'image/jpg', 'png'=>'image/png', 'x-png'=>'image/x-png', 'gif'=>'image/gif');
		$ext = self::getExt($filename);
		return $mimes[$ext];
	}

	public static function valid(string $filename) :bool
	{
		return in_array(self::getExt($filename), array('pjpeg','jpeg','jpg','png','x-png','gif'));
	}

	public function save(string $format = 'jpg') :Picture
	{
		$this->resizeImage($this->filename, $this->filename, 0, 0, $this->width, $this->height, $this->width, $this->height);
		return $this;
	}

	public function resize($targ_w, $targ_h, $targ_f = ''): static
    {
		if($targ_f == '')
		{
			$targ_f = $this->filename;
			$mime = $this->mime;
		} else
			$mime = $this->getMime($targ_f);

		list($width, $height, $type) = getimagesize($this->filename);

		$this->resizeImage($this->filename, $targ_f, 0, 0, $targ_w, $targ_h, $width, $height);
		return $this;
	}

	public function crop($width, $height, $targ_x, $targ_y, $targ_w, $targ_h, $targ_f = ''): static
    {
		if($targ_f == '')
		{
			$targ_f = $this->filename;
			$mime = $this->mime;
		} else
			$mime = $this->getMime($targ_f);

		$this->resizeImage($this->filename, $targ_f, $targ_x, $targ_y, $width, $height, $targ_w, $targ_h);
		return $this;
	}

	public static function resizeImage($img_f, $targ_f, $targ_x, $targ_y, $width, $height, $targ_w, $targ_h): void
	{
		switch(self::getMime($img_f))
		{
			case 'image/gif':
					$img_r = imagecreatefromgif($img_f);
					break;
			case 'image/pjpeg':
			case 'image/jpeg':
			case 'image/jpg':
					$img_r = imagecreatefromjpeg($img_f);
					break;
			case 'image/png':
			case 'image/x-png':
					$img_r = imagecreatefrompng($img_f);
					break;
  		}

		$targ_r = imagecreatetruecolor($width, $height);
		imagecopyresampled($targ_r, $img_r, 0, 0, $targ_x, $targ_y, $width, $height, $targ_w, $targ_h);

		switch(self::getMime($targ_f))
        {
			case 'image/gif':
					imagegif($targ_r, $targ_f);
					break;
			case 'image/pjpeg':
			case 'image/jpeg':
			case 'image/jpg':
					imagejpeg($targ_r, $targ_f, 90);
					break;
			case 'image/png':
			case 'image/x-png':
					imagepng($targ_r, $targ_f);
					break;
		}

		chmod($targ_f, 0777);
	}
}
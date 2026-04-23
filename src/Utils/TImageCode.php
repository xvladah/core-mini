<?php

/**
 * Třída pro generování kontrolního obrázku
 *
 * @name TImageCode
 * @version 1.1
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2025
 */

/*declare(strict_types=1);*/

class TImageCode
{
    const string BASIC_FONT     = __DIR__. DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Font'.DIRECTORY_SEPARATOR.'futura.otf';
    const string SECONDARY_FONT = __DIR__. DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Font'.DIRECTORY_SEPARATOR.'timesi.ttf';

    private string $session = 'verify';
    private ?GdImage $img;
    private array $fonts;

    public int $height;
    public int $width;
    public ?string $code;
    public int $quality = 75;

    public function __construct(int $width = 120, int $height = 35, ?string $sid = null)
    {
        if ($sid != '')
            $this->session = $sid;

        if(session_status() === PHP_SESSION_NONE)
            session_start();

        $this->height = $height;
        $this->width = $width;
        $this->fonts = [
            self::BASIC_FONT,
            self::SECONDARY_FONT,
        ];
    }

    public function setSessionName(string $sid) :TImageCode
    {
        $this->session = $sid;
        return $this;
    }

    public function addFont(string $font) :TImageCode
    {
        $this->fonts[] = $font;
        return $this;
    }

    private function genCode(): void
    {
        $this->code = '';
        $chars = 'AXYEW2345689';
        srand();
        for($i = 0; $i <= 3; $i++)
            $this->code .= $chars[rand(0,11)];

        $_SESSION[$this->session] = $this->code;
    }

    public function verifyCode (?string $gd_string) :bool
    {
        return (self::getCode() == $gd_string);
    }

    public function getCode(): ?string
    {
        return $_SESSION[$this->session];
    }

    private function genImage() : void
    {
        $this->img = imagecreatetruecolor($this->width, $this->height);

        $img_width = imagesx($this->img);
        $img_height = imagesy($this->img);

        // Define some common colors
        $black = imagecolorallocate($this->img, 0, 0, 0);
        $white = imagecolorallocate($this->img, 255, 255, 255);
        $red = imagecolorallocatealpha($this->img, rand(150,255), rand(0,10), rand(0,10), 75);
        $green = imagecolorallocatealpha($this->img, rand(0,10), rand(150,255), rand(0,10), 75);
        $blue = imagecolorallocatealpha($this->img, rand(0,10), rand(0,10), rand(150,255), 75);

        // Background
        imagefilledrectangle($this->img, 0, 0, $img_width, $img_height, $white);

        // Ellipses (helps prevent optical character recognition)
        imagefilledellipse($this->img, ceil(rand(5, $img_width - 5)), ceil(rand(0, $img_height)), 30, 30, $red);
        imagefilledellipse($this->img, ceil(rand(5, $img_width - 5)), ceil(rand(0, $img_height)), 30, 30, $green);
        imagefilledellipse($this->img, ceil(rand(5, $img_width - 5)), ceil(rand(0, $img_height)), 30, 30, $blue);

        // Borders
        imagefilledrectangle($this->img, 0, 0, $img_width, 0, $black);
        imagefilledrectangle($this->img, $img_width - 1, 0, $img_width - 1, $img_height - 1, $black);
        imagefilledrectangle($this->img, 0, 0, 0, $img_height - 1, $black);
        imagefilledrectangle($this->img, 0, $img_height - 1, $img_width, $img_height - 1, $black);

        $this->genCode();

        $c = $img_width / 7;
        $h = round($img_height / (1.1));

        $barva = ImageColorAllocate($this->img, 0, 0, rand(0,200));
        ImageTTFText($this->img, rand(15,22), rand(-20,30), round($c * 0.5), $h, $barva, $this->fonts[rand(0,1)], $this->code[0]);
        $barva = ImageColorAllocate($this->img, 50, 50, rand(0,200));
        ImageTTFText($this->img, rand(15,25), rand(-20,30), round($c * 2.2), $h, $barva, $this->fonts[rand(0,1)], $this->code[1]);
        $barva = ImageColorAllocate($this->img, 30, 60, rand(0,200));
        ImageTTFText($this->img, rand(15,20), rand(-20,30), round($c * 3.8), $h, $barva, $this->fonts[rand(0,1)], $this->code[2]);
        $barva = ImageColorAllocate($this->img, 200, 0, rand(0,100));
        ImageTTFText($this->img, rand(15,18), rand(-20,30), round($c * 5.4), $h, $barva, $this->fonts[rand(0,1)], $this->code[3]);

    }

    public function output() :void
    {
        $this->genImage();

        header('Content-type: image/jpeg');

        imagejpeg($this->img, null, $this->quality);
        imagedestroy($this->img);
    }
}
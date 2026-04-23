<?php

/**
 * Crypt for encrypt and decrypt string
 *
 * @package Utils
 * @name TCrypt
 * @category Crypt
 * @version 2.0
 * @author Vladimir Horky <vladimir.horky@tedom.com>
 * @copyright 2025 TEDOM a.s.
 * @licence Vladimir Horky, TEDOM a.s.
 */

declare(strict_types=1);

final class TCrypt
{
    private string $key = '1qazxdr5';
    private string $codes64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

    public function __construct(string $key = '')
    {
       if($key != '')
          $this->key = $key;
    }

    public function encrypt(string $text) :string
    {
       $text = trim($text);
       $result = '';
       for($i=0; $i<strlen($text); $i++) {
           $char = substr($text, $i, 1);
           $keychar = substr($this->key, ($i % strlen($this->key))-1, 1);
           $result .= chr(ord($char) ^ ord($keychar));
       }

       return $this->encode64($result);
    }

    public function decrypt(string $text) :string
    {
       $text = trim($text);
       $result = '';
       $text = $this->decode64($text);

       for($i=0; $i<strlen($text); $i++) {
           $char = substr($text, $i, 1);
           $keychar = substr($this->key, ($i % strlen($this->key))-1, 1);
           $result .= chr(ord($char) ^ ord($keychar));
       }

       return $result;
    }


    public function div(float $x, float $y) :float
    {
       if ($x == 0) return 0; else
       	  if ($y == 0) return 0; else
       		return ($x - ($x % $y)) / $y;
    }

    public function encode64(string $text) :string
    {
       $text = trim($text);
       $result = '';
       $a = 0;
       $b = 0;

      for($i=0; $i<strlen($text); $i++)
      {
         $x = ord($text[$i]);
         $b = $b * 256 + $x;
         $a += 8;

         while($a >= 6)
         {
            $a -= 6;
            $x = $this->div($b, (1 << $a));
            $b %= (1 << $a);
            $result .= $this->codes64[$x];
         }
      }

    if($a > 0) {
       $x = $b << (6 - $a);
       $result .= $this->codes64[$x];
    }

    return $result;
  }


    public function decode64(string $text) :string
    {
       $text = trim($text);
       $result = '';
       $a = 0;
       $b = 0;

       for($i=0; $i<strlen($text); $i++)
       {
          $x = strpos($this->codes64, $text[$i]);
          if($x !== false)
          {
             $b = $b * 64 + $x;
             $a += 6;

             if($a >= 8) {
                $a -= 8;
                $x = $b >> $a;
                $b %= (1 << $a);
                $x %= 256;
                $result .= chr($x);
             }
          }
        }

        return $result;
    }
}
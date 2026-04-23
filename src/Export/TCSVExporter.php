<?php

/**
 * TCSVExporter for export CSV
 *
 * @package Utils
 * @name TCSVExporter
 * @category CSV
 * @version 1.0
 * @author Vladimir Horky <vladimir.horky@tedom.com>
 * @copyright 2017 TEDOM a.s.
 * @licence Vladimir Horky, TEDOM a.s.
 */

// declare(strict_types=1);

class TCSVExporter
{
	private const string CRLF = "\n";
    private const string DELIMITER = ';';
    private const string QUALIFICATOR = '"';

	private bool $qualificator;

	public array $items = [];
	public string $content = '';

	public function __construct(bool $qualificator = false)
	{
		$this->qualificator = $qualificator;
	}

	public function add(array $columns) :TCSVExporter
	{
		$this->items[] = $columns;
		return $this;
	}

    public function addItems(array $items) :TCSVExporter
    {
        foreach($items as $item)
            $this->items[] = $item;

        return $this;
    }

	public function count() :int
	{
		return count($this->items);
	}

	public function length() :int
	{
		return mb_strlen($this->content);
	}

	public static function addCSVSlashes($str, bool $qualificator) :string
	{
		// odstraneni netisknutelnych znaku
		$out = preg_replace('/[\x00-\x1F]/', '', trim($str));

		if($qualificator)
		{
			if(!is_numeric($str) || preg_match('/^[01]+$/', $str))
				$out = self::QUALIFICATOR.str_replace(self::QUALIFICATOR, self::QUALIFICATOR.self::QUALIFICATOR, $out).self::QUALIFICATOR;
		} else {
			if(str_contains($out, self::DELIMITER) || str_contains($out, self::QUALIFICATOR) || str_contains($out, ' '))
				$out = self::QUALIFICATOR.str_replace(self::QUALIFICATOR, self::QUALIFICATOR.self::QUALIFICATOR, $out).self::QUALIFICATOR;
		}

		return $out;
	}

	private function build(string $encoding = 'windows-1250') : void
    {
		$this->content = '';

		foreach($this->items as $row)
		{
			if($this->content != '')
				$this->content .= self::CRLF;
				
			$columns = '';
			$add = false;
			foreach($row as $col)
			{
				if($add)
					$columns .= self::DELIMITER;
				else
					$add = true;

				$columns .= self::addCSVSlashes($col, $this->qualificator);
			}

			$this->content .= $columns;
		}

		if($encoding != 'utf-8')
			$this->content = iconv('utf-8', $encoding, $this->content);
		else
			$this->content = chr(0xEF).chr(0xBB).chr(0xBF).$this->content;

    }

	public function save(string $filename, bool $build = true): false|int
    {
		if($build)
            $this->build('utf-8');

		return file_put_contents($filename, $this->content, FILE_APPEND);
	}

	public function print(bool $build = true, string $charset = 'windows-1250'): void
    {
		if($build)
            $this->build($charset);

		header('Content-Type:text/plain;charset='.$charset);
		header('Cache-Control: private, max-age=0, must-revalidate');
		header('Pragma: public');

		echo $this->content;
	}

	public function output(string $filename, bool $build = true, string $charset = 'utf-8'): void
    {
		if($build)
            $this->build($charset);

		header('Content-Type:text/csv;charset='.$charset);
		header('Content-Disposition:attachment;filename="'.$filename.'"');
		header('Cache-Control: private, max-age=0, must-revalidate');
		header('Pragma: public');

		echo $this->content;
	}

}

<?php

/**
 * Třída pro práci s polem
 *
 * @name TArray
 * @version 2.2
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2019
 * 
 * version 2.2
 * - added array_to_javascript - generovani pole do pole jako retezce javascriptu
 * 
 * version 2.1
 * - added array_copy - kopirovani subpole ze zdrojoveho pole
 */

declare(strict_types=1);

class TArray
{
	public array $items = [];

	public function __construct()
	{
		$this->items = [];
	}

	public function clear(): void
    {
		$this->items = [];
	}

	/**
	 * Přidává prvek do pole na poslední místo
	 *
	 * @param mixed $item
	 * @return mixed ukazalte na prvek
	 */
	public function add(mixed $item): mixed
    {
		return $this->items[] = $item;
	}

	/**
	 * Vkládá prvek $item do pole na pozici $position
	 *
	 * @param mixed $item
	 * @return mixed ukazatel na prvek
	 */
	public function insert(mixed $item): mixed
    {
		return $this->items[] = $item;
	}

	/**
	 * Vkládá textovou hodnotu do pole pod klíčem $key
	 *
	 * @param string|int $key
	 * @param mixed $value
	 *
	 * @return mixed ukazatel na komponentu
	 */
	public function addHash(string|int $key, mixed $value): mixed
    {
		return $this->items[$key] = $value;
	}

	/**
	 * Maže prvek z pole podle klíče
	 *
	 * @param string|int $i
	 * @return bool vraci true nebo false
	 */
	public function delete(string|int $key): bool
    {
		$result = false;

		$zal = [];
		foreach($this->items as $mykey => $value)
			if($mykey != $key)
				$zal[$mykey] = $value;
			else
				$result = true;

		$this->items = $zal;

		return $result;
	}

	/**
	 * Vrací index pole dle hledaného klíče
	 *
	 * @param string|int $key
	 * @return int index prvku v poli
	 */
	public function indexOf(string|int $key) :int
	{
		$i = 0;
		foreach($this->items as $mykey => $value)
		{
			if($mykey == $key)
			{
				return $i;
			}
			$i++;
		}

		return -1;
	}

	/**
	 * Hledá existenci prvku v poli dle klíče
	 *
	 * @param string|int $key
	 * @return bool index prvku v poli
	 */
	public function exists(string|int $key) :bool
	{
		foreach($this->items as $mykey => $value)
		{
			if($mykey == $key)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Vrací počet prvnků v poli
	 *
	 * @return int vraci pocet prvku v poli
	 */
	public function count() :int
	{
		return count($this->items);
	}

	/**
	 * Generuje HTML z prvků, pokud obsahují funkci html()
	 *
	 * @return string
	 */
	public function html() :string
	{
		$return = '';

		foreach($this->items as $item)
			$return .= $item->html();

		return $return;
	}

	public function __toString()
	{
		return $this->html();
	}

    /**
     * Prejmenuje klic pri zachovani hodnoty
     *
     * @param array $array
     * @param string $old_key
     * @param string $new_key
     * @return array
     */
	public static function array_key_rename(array $array, string $old_key, string $new_key) :array
	{
		if(key_exists($old_key, $array))
		{
			if(!key_exists($new_key, $array))
			{
				$zal = $array[$old_key];
				$array[$new_key] = $zal;
				unset($array[$old_key]);
			}
		}
		
		return $array;
	}	
	
	/**
	 * Vrati klic a novou hodnotu pro pole $array1, kde se hodnoty ve stejnem klici lisi
	 * Jako zakladni pole se bere $array1 a k nemu se prirovnava $array2, 
	 * cili $array2 muze mit klicu a hodnot vice, nez je v $array1 a 
	 * nebude to mit na vysledek vliv. Pokud se hodnota lisi, vrati se hodnota $array2
	 * 
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */

	public static function array_diff_values(array $array1, array $array2) :array
	{
		$result = [];
		
		foreach($array1 as $key => $value)
			if(key_exists($key, $array2))
			{
				if($value != $array2[$key])	
					$result[$key] = $array2[$key];
			}
		
		return $result;
	}
	
	/**
	 * Vraci pole, ktere je kopii zdrojoveho pole. Offset urcuje startovni pozici
	 * a count urcuje kolik polozek pole se ma od zadane pozice zkopirovat
	 *
	 * @param array $source_array source_array
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	
	public static function array_copy(array $source_array, int $offset, int $count) :array
	{
		$result = [];
		
		$c = count($source_array);

		$from = 0;
		$to = $offset + $count;

		if($to > $c)
			$to = $c;
			
		foreach($source_array as $key => $value)
		{
			if($from >= $offset)
			{
				if($from < $to)
					$result[$key] = $value;
				else
					break;
			}
			
			$from++;
		}
			
		return $result;	
	}
	
	public static function array_to_javascript(array $array, int $number = 1) :string
	{
		$is_number = ($number == 1);		
		
		$result = '';
		
		foreach($array as $value)
		{
			if($result != '')
				$result .= ',';
			
			if($is_number)	
				$result .= $value;
			else
				$result .= '"'.$value.'"';
		}			
			
		return '['.$result.']';	
	}
	
	public static function array_to_paths(array $array, int $count) :array
	{
		$result = [];
			
		$p = 0;
		$i = 0;
		foreach($array as $key => $item)
		{
			if($i >= $count)
			{
				$i = 0;
				$p++;
				$result[$p] = [];
			}
				
			$result[$p][$key] = $item;
			$i++;
		}
		
		return $result;		
	}
}
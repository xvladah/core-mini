<?php

/**
 * Trida TList - metody pro spravu pole
 *
 * @name TList
 * @version 1.0
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 */

declare(strict_types=1);

class TList
{
	public array $items;

	public function __construct()
	{
		$this->items = [];
	}

	public function clear() :TList
	{
		$this->items = [];
		return $this;
	}

	public function isEmpty() :bool
	{
		return (count($this->items) == 0);
	}

	public function count() :int
	{
		return count($this->items);
	}

	public function delete(int $index) :TList
	{
		unset($this->items[$index]);
		return $this;
	}

	public function add(int $index, $object) :TList
	{
		$this->items[$index] = $object;
		return $this;
	}

	public function exists(int $index) :bool
	{
		return (key_exists($index, $this->items));
	}
}
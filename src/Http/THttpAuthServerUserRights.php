<?php

/**
 * Třída serveru pro prava uzivatele
 *
 * @name THttpAuthServerUser
 * @version 1.0
 * @author vladimir.horky
 * @copyright Vladimír, Horký, 2022.
 */

declare(strict_types=1);

class THttpAuthServerUserRights
{
	protected array $items = [];

	public function add(int $right_id, string $right_name): static
    {
		$this->items[$right_id] = $right_name;
		return $this;
	}
	
	public function hasRight(int $right_id): bool
    {
		return key_exists($right_id, $this->items);
	}
	
	public function getRightsList(int $right) :string
	{
		$result = '';
		
		if(key_exists($right, $this->items))
		{
			foreach($this->items[$right] as $item)
			{
				if($result != '')
					$result .= ',';
					
				$result .= $item;
			}
		}
		
		return $result;
	}
	
	public function getRightsArray(int $right) :array
	{
		$result = [];
		
		if(key_exists($right, $this->items))
		{
			foreach($this->items[$right] as $item)
				$result[] = $item;
		}
		
		return $result;
	}
	
	public function getMainRightsArray() :array
	{
		return array_keys($this->items);
	}

	public function load(TPDO $pdo, int $user_id)
	{
		$where = [
			'uzivatel_id'	=> $user_id,
			'<pravo_id'		=> TRights::ADMIN
		];
		
		try {
			$query = TBASEUzivatelePrava::getInstance($pdo)->prava($where);
			while($zaznam = $query->fetch())
			{
				if(!key_exists($zaznam['pravo_id'], $this->items))
					$this->items[$zaznam['pravo_id']] = [];
				
				$this->items[$zaznam['pravo_id']][] = $zaznam['objekt_id'];
			}
				
		} catch (Exception $e) {
			die('User Rights Error: '.TErrorsEx::formatException($e));
		}
		
		return $this;
	}
}

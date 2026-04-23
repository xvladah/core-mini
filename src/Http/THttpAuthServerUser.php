<?php

/**
 * Třída serveru pro uzivatele
 *
 * @name THttpAuthServerUser
 * @version 1.0
 * @author vladimir.horky
 * @copyright Vladimír, Horký, 2022.
 */

declare(strict_types=1);

class THttpAuthServerUser
{
	protected ?int $user_id;
	protected ?string $login;
	protected ?string $username;
	protected ?int $lang_id;
	
	public THttpAuthServerUserRights $rights;
	
	public function __construct(?int $user_id, ?string $login, ?string $username, ?int $lang_id)
	{
		$this->user_id	= $user_id;
		$this->login	= $login;
		$this->username	= $username;
		$this->lang_id	= $lang_id;

		$this->rights	= new THttpAuthServerUserRights();
		$this->rights->add(TRights::USER, TRights::USER_STR);
	}
	
	public function getUserId() :int
	{
		return $this->user_id;
	}
	
	public function getLogin() :string
	{
		 return $this->login;
	}
	
	public function getUserName() :string
	{
		return  $this->username;
	}
	
	public function getLangId() :int
	{
		return $this->lang_id;
	}
}
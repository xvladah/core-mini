<?php

/**
 * Třída serveru pro komunikace pres API
 *
 * @name THttpAuthServer
 * @version 2.0
 * @author vladimir.horky
 * @copyright Vladimír, Horký, 2022.
 *   
 * version 2.0
 * - added auth types
 * - added HttpAuthServerUser support
 * 
 * version 1.2 
 * - added getRequestMethod()
 */

declare(strict_types=1);

abstract class THttpAuthServer
{
	const int AUTH_TYPE_BASIC = 1;
	const string AUTH_TYPE_BASIC_STR = 'Basic';
	const int AUTH_TYPE_TOKEN = 2;
	const string AUTH_TYPE_TOKEN_STR = 'Bearer';

    const int AUTH_TYPE_TOKEN_REFRESH = 3;
    const string AUTH_TYPE_TOKEN_REFRESH_STR = 'Bearer';

	protected ?string $auth_code;
	protected int $auth_type = self::AUTH_TYPE_TOKEN;

	public ?THttpAuthServerUser $user;

    /**
     * @throws EHttpAuthServer
     * @throws Exception
     */
    public function login(?string $auth_code = null): static
    {
		if($auth_code == '')
		{
			$headers = apache_request_headers();

            if(isset($headers['Authorization']) && $headers['Authorization'] != '')
                $authorization = $headers['Authorization'];
            else
                if(isset($headers['WWW-Authenticate']) && $headers['WWW-Authenticate'] != '')
                    $authorization = $headers['WWW-Authenticate'];
                else
                    // kvuli moznosti chybne zadane polozky
                    if(isset($headers['Authentication']) && $headers['Authentication'] != '')
                        $authorization = $headers['Authentication'];
                    else
                        $authorization = '';
		} else {

			if(isset($_GET['auth_code']) && isset($_GET['debug']))
				$auth_code = mb_substr($_GET['auth_code'], 0, 80);

            $authorization = match ($this->auth_type) {
                self::AUTH_TYPE_TOKEN   => self::AUTH_TYPE_TOKEN_STR . ' ' . $auth_code,
                self::AUTH_TYPE_TOKEN_REFRESH => self::AUTH_TYPE_TOKEN_REFRESH_STR . ' ' . $auth_code,
                self::AUTH_TYPE_BASIC   => self::AUTH_TYPE_BASIC_STR . ' ' . $auth_code,
                default                 => throw new Exception(get_class($this) . ': Unknown authorization type!', -1),
            };
		}

		$matches = [];
		if(preg_match($this->getAuthorizationMask(), $authorization, $matches))
		{
			$this->$auth_code = $matches[1];
			if($this->Authenticate($this->$auth_code) === false)
				$this->user = null;
		} else
			throw new EHttpAuthServer(__CLASS__.'.'.__FUNCTION__.'('.__LINE__.'): Unsupported authorization format!', -2);

		return $this;
	}

	abstract protected function Authenticate(?string $token): void;

	public function setAuthorizationType(int $auth_type) :self
	{
		$this->auth_type = $auth_type;
		return $this;
	}

	public function getAuthorizationType() :int
	{
		return $this->auth_type;
	}

	public function isAuthCode(): bool
    {
		return $this->auth_code !== null;
	}

	public function isAuthenticated() :bool
	{
		return ($this->user !== null);
	}

    /**
     * @throws EHttpAuthServer
     */
    protected function getAuthorizationText() :string
	{
        return match ($this->auth_type) {
            self::AUTH_TYPE_TOKEN   => self::AUTH_TYPE_TOKEN_STR,
            self::AUTH_TYPE_TOKEN_REFRESH => self::AUTH_TYPE_TOKEN_REFRESH_STR,
            self::AUTH_TYPE_BASIC   => self::AUTH_TYPE_BASIC_STR,
            default                 => throw new EHttpAuthServer(__CLASS__ . '.' . __FUNCTION__ . '(' . __LINE__ . '): Unknown authorization type', -5),
        };
	}

    /**
     * @throws EHttpAuthServer
     */
    protected function getAuthorizationMask() :string
	{
        return match ($this->auth_type) {
            self::AUTH_TYPE_TOKEN           => '/' . self::AUTH_TYPE_TOKEN_STR . '[ ]*([a-z0-9]{1,80})/i',
            self::AUTH_TYPE_TOKEN_REFRESH   => '/' . self::AUTH_TYPE_TOKEN_REFRESH_STR . '[ ]*([a-z0-9]{1,80})/i',
            self::AUTH_TYPE_BASIC           => '/' . self::AUTH_TYPE_BASIC_STR . '[ ]*([a-z0-9]{1,150})/i',
            default                         => throw new EHttpAuthServer(__CLASS__ . '.' . __FUNCTION__ . '(' . __LINE__ . '): Unknown authorization type', -6),
        };
	}

	public function getRequestMethod()
	{
		return $_SERVER['REQUEST_METHOD'];
	}
	
	public function createUser(?int $user_id, ?string $login, ?string $name, ?int $lang_id = -1): self
	{
		$this->user = new THttpAuthServerUser($user_id, $login, $name, $lang_id);
		return $this;
	}
	
	public function getUserId(): ?int
    {
		if($this->user !== null)
			return $this->user->getUserId();
		else
			return null;
	}
	
	public function getLogin(): ?string
    {
		if($this->user !== null)
			return $this->user->getLogin();
		else
			return null;
	}
	
	public function getUserName(): ?string
    {
		if($this->user !== null)
			return $this->user->getUserName();
		else
			return null;
	}
}

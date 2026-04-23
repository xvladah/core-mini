<?php

/**
* Třída serveru pro Open Authentication
*
* @name THttpOAuthServer
* @version 1.2
* @author vladimir.horky
* @copyright Vladimír, Horký, 2018.
*
* version 1.2
* - added getRequestMethod()
*/

declare(strict_types=1);

abstract class THttpOAuthServer
{
    private ?string $token_code = null;

    protected ?int $user_id	= null;
    protected ?string $login	= null;
    protected ?string $name		= null;
    protected ?int $lang_id	= null;

    public function login(?string $token = null): self
    {
        if(isset($_GET['token']) && isset($_GET['debug']))
            $authorization = 'bearer '.mb_substr($_GET['token'], 0, 40);
        else
            if($token === null)
            {
                $headers 	   = apache_request_headers();
                $authorization = $headers['Authorization'];
            } else
                $authorization = 'bearer '.$token;

        $matches = [];
        if(preg_match('/bearer[ ]*([a-z0-9]+)/i', $authorization, $matches))
        {
            $this->token_code = $matches[1];
            if($this->Authenticate($this->token_code) === false)
            {
                $this->setUser(null, null, null, null);
            }
        } else
            THttpResponse::SendCode(401); // UnAuthorized

        return $this;
    }

    public function isAuthenticationBearer(): bool
    {
        return $this->token_code !== null;
    }

    public function getRequestMethod(): ?string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    abstract protected function Authenticate(?string $token_code): void;

    public function setUser(?int $user_id, ?string $login, ?string $name, ?int $lang_id = -1): self
    {
        $this->user_id 	= $user_id;
        $this->login	= $login;
        $this->name		= $name;
        $this->lang_id	= $lang_id;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getUserName(): ?string
    {
        return $this->name;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function getLangId(): ?int
    {
        return $this->lang_id;
    }
}

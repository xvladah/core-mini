<?php

/**
* Třída klienta pro Open Authentication
*
* @name THttpOAuthClient
* @version 3.0
* @author vladimir.horky
* @copyright Vladimír, Horký, 2018.
*
* version 3.0
* - changed private vars
*
* version 2.0
* - changed class name
*
* version 1.2
* trim remaked
*/

declare(strict_types=1);

abstract class THttpAuthClient
{
    const int AUTH_TYPE_NONE  = 0;
    const int AUTH_TYPE_BASIC = 1;
    const string AUTH_TYPE_BASIC_STR = 'Basic';
    const int AUTH_TYPE_TOKEN = 2;
    const string AUTH_TYPE_TOKEN_STR = 'Bearer';

    private CurlHandle|bool $handle;
    public ?string $last_error = null;
    private ?string $token_code = null;
    private ?string $username	= null;
    private ?string $password	= null;
    private int $auth_type	= self::AUTH_TYPE_TOKEN;

    protected string $scheme	= 'https';
    protected string $host		= '';
    protected string $port		= '';
    protected string $path		= '';
    protected string $agent 	= '';
    public bool $ssl_verify = false;

    public int $timeout	= 15000; // 15 sec.
    public int $timeout_connect = 2;

    public string $encoding = 'utf-8';
    public string $accept = THttpConsts::MIME_TEXT;

    public bool $debug = false;

    private string $ip 	 		= '';
    private bool $use_remote_ip	= false;

    private array $headers;

    public function __construct(string $host, ?string $path = null, string $agent = 'HttpAuthClient Agent')
    {
        if(($pos = strpos($host, '://')) !== false)
        {
            $this->host 	= self::trim(substr($host, $pos + 3));
            $this->scheme	= substr($host, 0, $pos + 3);
        } else
            $this->host 	= self::trim($host);

        $matches = [];
        if(preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $this->host, $matches))
        {
            $this->ip = $matches[1];
            $this->use_remote_ip = ($this->ip != '');
        }

        if($path != '')
            $this->path	= self::trim($path);

        $this->agent = $agent;
        $this->headers = [];
    }

    public function setDebug(bool $debug = true) :THttpAuthClient
    {
        $this->debug = $debug;
        return $this;
    }

    public function setAuthBasic(?string $username, ?string $password) :THttpAuthClient
    {
        $this->auth_type = self::AUTH_TYPE_BASIC;
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    public function setAuthBearer(?string $token) :THttpAuthClient
    {
        $this->auth_type = self::AUTH_TYPE_TOKEN;
        $this->token_code = $token;

        return $this;
    }

    public function setAuthNone() :THttpAuthClient
    {
        $this->auth_type = self::AUTH_TYPE_NONE;

        return $this;
    }

    private static function trim(string $str): string
    {
        return trim($str, "\t\n\r/ ");
    }

    public function setScheme(?string $scheme) :THttpAuthClient
    {
        if($scheme != '')
        {
            $this->scheme = $scheme;
            if(!(str_contains($this->scheme, '://')))
                $this->scheme .= '://';
        }

        return $this;
    }

    public function setPort(int $port) :THttpAuthClient
    {
        if($port != '')
            $this->port = ':'.$port;
        else
            $this->port = '';

        return $this;
    }

    private function buildHeader(string $content_type = ''): array
    {
        $this->headers = [
            'Accept: '.$this->accept,
            'Host: '.$this->host,
            'User-Agent: '.$this->agent
        ];

        switch($this->auth_type)
        {
            case self::AUTH_TYPE_BASIC :
                    $this->headers[] = 'Authorization: '.self::AUTH_TYPE_BASIC_STR.' '.base64_encode($this->username.':'.$this->password);
                break;

            case self::AUTH_TYPE_TOKEN :
                    $this->headers[] = 'Authorization: '.self::AUTH_TYPE_TOKEN_STR.' '.$this->token_code;
                break;
        }

        if($content_type != '')
            $headers = array_merge($this->headers, ['Content-Type:'.$content_type]);
        else
            $headers = $this->headers;

        if($this->debug)
        {
            echo 'HEADERS'.PHP_EOL;
            print_r($headers);
            echo PHP_EOL;
        }

        return $headers;
    }

    protected function http_build_url(string $query, array $params = []) :string
    {
        $url = $this->scheme.$this->host.$this->port;

        if($this->path != '')
            $url .= '/'.$this->path;

        $trimQuery = self::trim($query);
        if($trimQuery != '')
            $url .= '/'.$trimQuery;

        if(count($params) > 0)
        {
            if(str_contains($url, '?'))
                $url .= '&'.http_build_query($params);
            else {
                if(!str_contains($query, '.'))
                    $url .= '/';

                $url .= '?'.http_build_query($params);
            }
        } else
            if(!str_contains($query, '.'))
                $url .= '/';

        if($this->debug)
        {
            echo 'URL: ';
            print_r($url);
            echo PHP_EOL;
        }

        return $url;
    }

    public function getConfigPath(): string
    {
        $result = $this->scheme.$this->host.$this->port;
        if($this->path != '')
            $result .= '/'.$this->path;

        return $result.'/';
    }

    protected function _get(int &$http_code, string $query, array $params = []) :string|bool
    {
        $this->handle = curl_init();

        $url = $this->http_build_url($query, $params);
        $headers = $this->buildHeader();

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, $this->ssl_verify);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, $this->ssl_verify);

        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, true);

        if($this->use_remote_ip && $this->ip != '')
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, ['REMOTE_ADDR: '.$this->ip, 'HTTP_X_FORWARDED_FOR: '.$this->ip]);

        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $this->timeout_connect);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, $this->timeout);

        $result = curl_exec($this->handle);

        if(!$result)
            $this->last_error = curl_error($this->handle);
        else
            $this->last_error = null;

        $http_code	= curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        if($this->debug)
            print_r(curl_getinfo($this->handle));

        curl_close($this->handle);

        return $result;
    }

    protected function _post(int &$http_code, string $query, array $params = [], array $fields = []) :string|bool
    {
        $url = $this->http_build_url($query, $params);

        if(is_array($fields))
        {
            $http_build_query = http_build_query($fields);
            $headers = $this->buildHeader(THttpConsts::MIME_URLENCODED.';charset='.$this->encoding);
        } else {
            $http_build_query = $fields;
            $headers = $this->buildHeader(THttpConsts::MIME_JSON);
        }

        $this->handle = curl_init();

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, $this->ssl_verify);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, $this->ssl_verify);

        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, true);

        if($this->use_remote_ip && $this->ip != '')
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, ['REMOTE_ADDR: '.$this->ip, 'HTTP_X_FORWARDED_FOR: '.$this->ip]);

        curl_setopt($this->handle, CURLOPT_POST, true);
        //curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $http_build_query);

        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $this->timeout_connect);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, $this->timeout);

        $result 	= curl_exec($this->handle);

        if(!$result)
            $this->last_error = curl_error($this->handle);
        else
            $this->last_error = null;

        $http_code	= curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        if($this->debug)
            print_r(curl_getinfo($this->handle));

        curl_close($this->handle);

        return $result;
    }

    protected function _put(int &$http_code, string $query, array $params = [], array $fields = []) :string|bool
    {
        $this->handle = curl_init();

        $url = $this->http_build_url($query, $params);

        if($this->debug)
        {
            echo 'FIELDS'.PHP_EOL;
            print_r($fields);
            echo PHP_EOL;
        }

        if(is_array($fields))
        {
            $http_build_query = http_build_query($fields);
            $headers = $this->buildHeader(THttpConsts::MIME_URLENCODED.';charset='.$this->encoding);
        } else {
            $http_build_query = $fields;
            $headers = $this->buildHeader(THttpConsts::MIME_JSON);
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, $this->ssl_verify);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, $this->ssl_verify);

        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, true);

        if($this->use_remote_ip && $this->ip != '')
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, ['REMOTE_ADDR: '.$this->ip, 'HTTP_X_FORWARDED_FOR: '.$this->ip]);

        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $http_build_query);

        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $this->timeout_connect);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, $this->timeout);

        $result 	= curl_exec($this->handle);

        if(!$result)
            $this->last_error = curl_error($this->handle);
        else
            $this->last_error = null;

        $http_code	= curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        if($this->debug)
            print_r(curl_getinfo($this->handle));

        curl_close($this->handle);

        return $result;
    }

    protected function _delete(int &$http_code, string $query, array $params = [], array $fields = []) :string|bool
    {
        $this->handle = curl_init();

        $url = $this->http_build_url($query, $params);

        if(is_array($fields))
        {
            $http_build_query = http_build_query($fields);
            $headers = $this->buildHeader(THttpConsts::MIME_URLENCODED.';charset='.$this->encoding);
        } else {
            $http_build_query = $fields;
            $headers = $this->buildHeader(THttpConsts::MIME_JSON);
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, $this->ssl_verify);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, $this->ssl_verify);

        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, true);

        if($this->use_remote_ip && $this->ip != '')
            curl_setopt($this->handle, CURLOPT_HTTPHEADER, ['REMOTE_ADDR: '.$this->ip, 'HTTP_X_FORWARDED_FOR: '.$this->ip]);

        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $http_build_query);

        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT, $this->timeout_connect);
        curl_setopt($this->handle, CURLOPT_TIMEOUT, $this->timeout);

        $result 	= curl_exec($this->handle);

        if(!$result)
            $this->last_error = curl_error($this->handle);
        else
            $this->last_error = null;

        $http_code	= curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        if($this->debug)
            print_r(curl_getinfo($this->handle));

        curl_close($this->handle);

        return $result;
    }
}
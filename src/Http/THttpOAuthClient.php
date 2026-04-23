<?php

/**
* Třída klienta pro Open Authentication
*
* @name THttpOAuthClient
* @version 1.2
* @author vladimir.horky
* @copyright Vladimír, Horký, 2018.
*
* version 1.2
* trim remaked
*/

declare(strict_types=1);

abstract class THttpOAuthClient
{
    private string $token_code;

    protected string $scheme	= 'https';
    protected string $host		= '';
    protected string $port		= '';
    protected string $path		= '';
    protected string $agent 	= '';

    public int $timeout	= 15000; // 15 sec.
    public int $timeout_connect = 2;

    public string $encoding = 'utf-8';

    private mixed $ip 	 		= '';
    private bool $use_remote_ip	= false;

    private array $headers;

    public function __construct(string $host, string $path, string $token_code, string $agent = 'HttpOAuthClient Agent')
    {
        if(($pos = strpos($host, '://')) !== false)
        {
            $this->host 	= self::trim(substr($host, $pos + 3));
            $this->scheme	= substr($host, 0, $pos + 3);
        } else
            $this->host 	= self::trim($host);

        $matches = [];
        if(preg_match('/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $this->host.'/fdsfsd/', $matches))
        {
            $this->ip = $matches[1];
            $this->use_remote_ip = ($this->ip != '');
        }

        if($path != null)
            $this->path 	= self::trim($path);

        $this->agent		= $agent;
        $this->token_code 	= $token_code;

        $this->headers = [
            'Accept:'.THttpConsts::MIME_TEXT,
            'Host:'.$this->host,
            'Authorization:Bearer '.$this->token_code,
            'User-Agent:'.$this->agent
        ];
    }

    private static function trim(?string $str): string
    {
        return trim($str, "\t\n\r/ ");
    }

    public function setScheme(?string $scheme) :THttpOAuthClient
    {
        if($scheme != '')
        {
            $this->scheme = $scheme;
            if(!(str_contains($this->scheme, '://')))
                $this->scheme .= '://';
        }

        return $this;
    }

    public function setPort(?int $port) :THttpOAuthClient
    {
        if($port != '')
            $this->port = ':'.$port;
        else
            $this->port = '';

        return $this;
    }

    private function buildHeader(string $content_type = ''): array
    {
        if($content_type != '')
            return array_merge($this->headers, ['Content-Type:'.$content_type]);
        else
            return $this->headers;
    }

    protected function http_build_url(string $query, array $params = []) :string
    {
        $url = $this->scheme.$this->host.$this->port.'/'.$this->path.'/'.self::trim($query);
        if(count($params) > 0)
        {
            if(strpos($url, '?') !== false)
                $url .= '&'.http_build_query($params);
            else {
                if(strpos($query, '.') === false)
                    $url .= '/';

                $url .= '?'.http_build_query($params);
            }
        } else
            if(strpos($query, '.') === false)
                $url .= '/';

        return $url;
    }

    public function getConfigPath(): string
    {
        return $this->scheme.$this->host.$this->port.'/'.$this->path.'/';
    }

    protected function _get(int &$http_code, string $query, array $params = []) :string
    {
        $ch = curl_init();

        $url = $this->http_build_url($query, $params);
        $headers = $this->buildHeader();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if($this->use_remote_ip && $this->ip != '')
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['REMOTE_ADDR: '.$this->ip, 'HTTP_X_FORWARDED_FOR: '.$this->ip]);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout_connect);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $result 	= curl_exec($ch);
        $http_code	= curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //	print_r(curl_getinfo($ch));

        curl_close($ch);

        return $result;
    }

    protected function _post(int &$http_code, string $query, array $params = [], $fields = []) :string
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
/*
        echo $url."\n";
        echo $http_build_query."\n";
        print_r($headers);
*/		
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if($this->use_remote_ip && $this->ip != '')
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['REMOTE_ADDR: '.$this->ip, 'HTTP_X_FORWARDED_FOR: '.$this->ip]);

        curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $http_build_query);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout_connect);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $result 	= curl_exec($ch);
        $http_code	= curl_getinfo($ch, CURLINFO_HTTP_CODE);

        //print_r(curl_getinfo($ch));

        curl_close($ch);

        return $result;
    }

    protected function _put(int &$http_code, string $query, array $params = [], $fields = []) :string
    {
        $ch = curl_init();

        $url = $this->http_build_url($query, $params);

        //print_r($fields);

        if(is_array($fields))
        {
            $http_build_query = http_build_query($fields);
            $headers = $this->buildHeader(THttpConsts::MIME_URLENCODED.';charset='.$this->encoding);
        } else {
            $http_build_query = $fields;
            $headers = $this->buildHeader(THttpConsts::MIME_JSON);
        }

    /*	 echo $url."\n";
         echo $http_build_query."\n";
         print_r($headers);
    */

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if($this->use_remote_ip && $this->ip != '')
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['REMOTE_ADDR: '.$this->ip, 'HTTP_X_FORWARDED_FOR: '.$this->ip]);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $http_build_query);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout_connect);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $result 	= curl_exec($ch);
        $http_code	= curl_getinfo($ch, CURLINFO_HTTP_CODE);

    //	print_r(curl_getinfo($ch));

        curl_close($ch);

        return $result;
    }

    protected function _delete(int &$http_code, string $query, array $params = [], $fields = []) :string
    {
        $ch = curl_init();

        $url = $this->http_build_url($query, $params);

        if(is_array($fields))
        {
            $http_build_query = http_build_query($fields);
            $headers = $this->buildHeader(THttpConsts::MIME_URLENCODED.';charset='.$this->encoding);
        } else {
            $http_build_query = $fields;
            $headers = $this->buildHeader(THttpConsts::MIME_JSON);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if($this->use_remote_ip && $this->ip != '')
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['REMOTE_ADDR: '.$this->ip, 'HTTP_X_FORWARDED_FOR: '.$this->ip]);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $http_build_query);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout_connect);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        $result 	= curl_exec($ch);
        $http_code	= curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $result;
    }
}
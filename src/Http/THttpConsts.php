<?php

declare(strict_types=1);

class THttpConsts
{
    const int HTTP_CODE_200 = 200;
    const int HTTP_CODE_201 = 201;
    const int HTTP_CODE_202 = 202;
    const int HTTP_CODE_204 = 204;
    const int HTTP_CODE_300 = 300;
    const int HTTP_CODE_301 = 301;
    const int HTTP_CODE_302 = 302;
    const int HTTP_CODE_400 = 400;
    const int HTTP_CODE_401 = 401;
    const int HTTP_CODE_403 = 403;
    const int HTTP_CODE_404 = 404;
    const int HTTP_CODE_405 = 405;
    const int HTTP_CODE_406 = 406;
    const int HTTP_CODE_409 = 409;
    const int HTTP_CODE_412 = 412;
    const int HTTP_CODE_415 = 415;
    const int HTTP_CODE_500 = 500;
    const int HTTP_CODE_501 = 501;
    const int HTTP_CODE_502 = 502;
    const int HTTP_CODE_503 = 503;
    const int HTTP_CODE_504 = 504;

    const array HTTP_CODES = [

        100 => 'Continue',
        101 => 'Switching Protocols',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ];

    const string MIME_JSON 		    = 'application/json';
    const string MIME_URLENCODED	= 'application/x-www-form-urlencoded';
    const string MIME_TEXT 		    = 'text/plain';
    const string MIME_HTML			= 'text/html';

    const string REQUEST_TYPE_GET		= 'GET';
    const string REQUEST_TYPE_POST		= 'POST';
    const string REQUEST_TYPE_DELETE	= 'DELETE';
    const string REQUEST_TYPE_PUT		= 'PUT';
    /**
     * Vrací texotvý popis stavu HTTP
     *
     * @param int $http_code
     * @return string
     */
    public static function getHttpCodeDesc(int $http_code) :string
    {
        if(key_exists($http_code, self::HTTP_CODES))
            return self::HTTP_CODES[$http_code];
        else
            return 'Unknown code '.$http_code;
    }
}




<?php

class EHttpAuthServer extends Exception
{
    const int HTTP_INTERNAL_SERVER_ERROR = 500;
    const string HTTP_INTERNAL_SERVER_ERROR_TEXT = 'Internal Server Error';
    const int HTTP_UNAUTHORIZED 		 = 401;
    const string HTTP_UNAUTHORIZED_TEXT  = 'Unauthorized';
    const int HTTP_FORBIDDEN 			 = 403;
    const string HTTP_FORBIDDEN_TEXT	 = 'Forbidden';
}

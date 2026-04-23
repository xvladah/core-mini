<?php

/**
* Trida chybovych hlaseni a kodu
*
* @name TErrors
* @version 1.2
* @author vladimir.horky
* @copyright Vladimír Horký, 2019
*/

class TErrors
{
    const int UNKNOWN						= 0;

    const int OK							= 1;
    const string OK_TEXT					= 'OK';

    const int ERR							= -1;
    const string ERR_TEXT					= 'Obecná chyba';

    const int ERR_AUTHENTICATION			= -2;
    const string ERR_AUTHENTICATION_TEXT	= 'Chyba v autentizaci';
    const int ERR_AUTHENTICATION_LOGIN		= -3;
    const string ERR_AUTHENTICATION_LOGIN_TEXT	= 'Chybný login nebo heslo';
    const int ERR_AUTHENTICATION_TOKEN		= -4;
    const string ERR_AUTHENTICATION_TOKEN_TEXT	= 'Chybný token autentizace';

    const int ERR_DATABASE_CONNECTION		= -10;
    const string ERR_DATABASE_CONNECTION_TEXT 	= 'Chyba připojení k databázi';
    const int ERR_LDAP_CONNECTION			= -20;
    const string ERR_LDAP_CONNECTION_TEXT		= 'Chyba připojení k LDAP';
    const int ERR_LDAP_AUTHENTICATION		= -20;
    const string ERR_LDAP_AUTHENTICATION_TEXT	= 'Chyba v LDAP autentizaci';

    const int ERR_BAD_PARAMS 				= -30;
    const string ERR_BAD_PARAMS_TEXT		= 'Chyba v URL parametrech';
    const int ERR_BAD_PARAMS_COUNT			= -40;
    const string ERR_BAD_PARAMS_COUNT_TEXT	= 'Chybný počet URL parametrů';
    const int ERR_BAD_PARAMS_FORMAT			= -50;
    const string ERR_BAD_PARAMS_FORMAT_TEXT	= 'Chybný formát URL parametru';

    const int ERR_BAD_INPUT_PARAMS			= -60;
    const string ERR_BAD_INPUT_PARAMS_TEXT	= 'Chyba ve vstupních parametrech';
    const int ERR_BAD_INPUT_PARAMS_COUNT	= -70;
    const string ERR_BAD_INPUT_PARAMS_COUNT_TEXT	= 'Chybný počet vstupních parametrech';
    const int ERR_BAD_INPUT_PARAMS_FORMAT		= -80;
    const string ERR_BAD_INPUT_PARAMS_FORMAT_TEXT 	= 'Chybný formát ve vstupních parametrech';

    const int ERR_NO_RIGHT					= -100;
    const string ERR_NO_RIGHT_TEXT			= 'K dané operaci nemáte oprávnění';
    const int ERR_NO_RIGHT_DELETE			= -110;
    const string ERR_NO_RIGHT_DELETE_TEXT	= 'Nemáte oprávnění k mazázní';
    const int ERR_NO_RIGHT_EDIT				= -120;
    const string ERR_NO_RIGHT_EDIT_TEXT		= 'Nemáte oprávnění k editaci';
    const int ERR_NO_RIGHT_MODIFY			= -130;
    const string ERR_NO_RIGHT_MODIFY_TEXT	= 'Nemáte oprávnění k modifikaci';
    const int ERR_NO_RIGHT_VIEW				= -140;
    const string ERR_NO_RIGHT_VIEW_TEXT		= 'Nemáte oprávnění k prohlížení';

    const int ERR_NO_RIGHT_ACTION			= -180;
    const string ERR_NO_RIGHT_ACTION_TEXT	= 'Nemáte oprávnění k provedení akce';
    const int ERR_NO_RIGHT_RUN				= -190;
    const string ERR_NO_RIGHT_RUN_TEXT			= 'Nemáte oprávnění ke spuštění';

    const int ERR_FORM_MISTAKES				= -200;
    const string ERR_FORM_MISTAKES_TEXT		= 'Formulář obsahuje chyby';

    const int ERR_FILE						= -300;
    const string ERR_FILE_TEXT					= 'Obecná chyba souboru';
    const int ERR_FILE_FORMAT				= -310;
    const string ERR_FILE_FORMAT_TEXT		= 'Chybný formát souboru';
    const int ERR_FILE_SIZE					= -320;
    const string ERR_FILE_SIZE_TEXT			= 'Chybná velikost souboru';
    const int ERR_FILE_NAME					= -330;
    const string ERR_FILE_NAME_TEXT			= 'Chybný název souboru';
    const int ERR_FILE_EXTENSION			= -340;
    const string ERR_FILE_EXTENSION_TEXT	= 'Chybná přípona souboru';
    const int ERR_FILE_DESTINATION			= -350;
    const string ERR_FILE_DESTINATION_TEXT	= 'Chybné umístění souboru';
    const int ERR_FILE_READ					= -360;
    const string ERR_FILE_READ_TEXT			= 'Chyba čtení souboru';
    const int ERR_FILE_WRITE				= -370;
    const string ERR_FILE_WRITE_TEXT		= 'Chyba zápisu do souboru';
    const int ERR_FILE_COPY					= -380;
    const string ERR_FILE_COPY_TEXT			= 'Soubor se nepovedlo zkopírovat';
    const int ERR_FILE_MOVE					= -390;
    const string ERR_FILE_MOVE_TEXT			= 'Soubor se nepovedlo přemístit';
    const int ERR_FILE_NOTEXIST				= -400;
    const string ERR_FILE_NOTEXIST_TEXT		= 'Soubor neexistuje';
    const int ERR_FILE_UPLOAD				= -410;
    const string ERR_FILE_UPLOAD_TEXT		= 'Soubor se nepovedlo nahrát na server';
    const int ERR_FILE_ARCHIVE				= -420;
    const string ERR_FILE_ARCHIVE_TEXT		= 'Soubor se nepovedlo zabalit';
    const int ERR_FILE_EXTRACT				= -430;
    const string ERR_FILE_EXTRACT_TEXT		= 'Soubor se nepovedlo rozbalit';
    const int ERR_FILE_NOTFOUND				= -440;
    const string ERR_FILE_NOTFOUND_TEXT		= 'Soubor nebyl nenalezen';
    const int ERR_FILE_RUN					= -450;
    const string ERR_FILE_RUN_TEXT			= 'Soubor nelze spustit';

    const int ERR_DATABASE 					= -500;
    const string ERR_DATABASE_TEXT  		= 'Chyba databáze';
    const int ERR_DATABASE_VALUE			= -510;
    const string ERR_DATABASE_VALUE_TEXT 	= 'Chyba databáze, hodnota nenalezena';
    const int ERR_DATABASE_VALUES			= -520;
    const string ERR_DATABASE_VALUES_TEXT 	= 'Chyba databáze, nalezeno více hodnot';
    const int ERR_DATABASE_QUERY			= -530;
    const string ERR_DATABASE_QUERY_TEXT	= 'Chyba databáze, nesprávný SQL dotaz';
    const int ERR_DATABASE_INSERT			= -540;
    const string ERR_DATABASE_INSERT_TEXT	= 'Chyba databáze během vkládání dat';
    const int ERR_DATABASE_UPDATE			= -550;
    const string ERR_DATABASE_UPDATE_TEXT	= 'Chyba databáze během aktualizace dat';
    const int ERR_DATABASE_DELETE			= -560;
    const string ERR_DATABASE_DELETE_TEXT	= 'Chyba databáze během mazání dat';
    const int ERR_DATABASE_FUNCTION			= -570;
    const string ERR_DATABASE_FUNCTION_TEXT	= 'Chyba databáze během vykonávání funkce';
    const int ERR_DATABASE_PROCEDURE		= -580;
    const string ERR_DATABASE_PROCEDURE_TEXT= 'Chyba databáze během vykonávání procedury';
    const int ERR_DATABASE_SEARCH			= -590;
    const string ERR_DATABASE_SEARCH_TEXT	= 'Chyba databáze při hledání hodnot';

    const int ERR_SMTP_NO_SEND				= -600;
    const string ERR_SMTP_NO_SEND_TEXT		= 'E-mail se nepodařilo odeslat';

    public static function getMessage(int|string $code)
    {
        if(!function_exists('__'))
        {
           function __($a,$b)
           {
                return $b;
           }
        }

        if($code < -10)
            $code = round($code, -1);

        switch($code)
        {
            case self::OK						: $message = __('err.ok', self::OK_TEXT); break;

            case self::ERR						: $message = __('err.general_error', self::ERR_TEXT); break;

            case self::ERR_AUTHENTICATION		: $message = __('err.general_error', self::ERR_AUTHENTICATION_TEXT); break;
            case self::ERR_AUTHENTICATION_LOGIN	: $message = __('err.general_error', self::ERR_AUTHENTICATION_LOGIN_TEXT); break;
            case self::ERR_AUTHENTICATION_TOKEN	: $message = __('err.general_error', self::ERR_AUTHENTICATION_TOKEN_TEXT); break;

            case self::ERR_DATABASE_CONNECTION 	: $message = __('err.connection_database', self::ERR_DATABASE_CONNECTION_TEXT); break;
            case self::ERR_LDAP_CONNECTION		: $message = __('err.connection_ldap', self::ERR_LDAP_CONNECTION_TEXT); break;
            case self::ERR_LDAP_AUTHENTICATION	: $message = __('err.connection_ldap', self::ERR_LDAP_AUTHENTICATION_TEXT); break;

            case self::ERR_BAD_PARAMS 			: $message = __('err.url_params', self::ERR_BAD_PARAMS_TEXT); break;
            case self::ERR_BAD_PARAMS_COUNT		: $message = __('err.url_params_count', self::ERR_BAD_PARAMS_COUNT_TEXT); break;
            case self::ERR_BAD_PARAMS_FORMAT	: $message = __('err.url_params_format', self::ERR_BAD_PARAMS_FORMAT_TEXT); break;

            case self::ERR_BAD_INPUT_PARAMS			: $message = __('err.input_params', self::ERR_BAD_INPUT_PARAMS_TEXT); break;
            case self::ERR_BAD_INPUT_PARAMS_COUNT	: $message = __('err.input_params_count', self::ERR_BAD_INPUT_PARAMS_COUNT_TEXT); break;
            case self::ERR_BAD_INPUT_PARAMS_FORMAT	: $message = __('err.input_params_format', self::ERR_BAD_INPUT_PARAMS_FORMAT_TEXT); break;

            case self::ERR_NO_RIGHT				: $message = __('err.no_right_operation', self::ERR_NO_RIGHT_TEXT); break;
            case self::ERR_NO_RIGHT_DELETE		: $message = __('err.no_right_delete', self::ERR_NO_RIGHT_DELETE_TEXT); break;
            case self::ERR_NO_RIGHT_EDIT		: $message = __('err.no_right_edit', self::ERR_NO_RIGHT_EDIT_TEXT); break;

            case self::ERR_NO_RIGHT_MODIFY		: $message = __('err.no_right_modify', self::ERR_NO_RIGHT_MODIFY_TEXT); break;
            case self::ERR_NO_RIGHT_VIEW		: $message = __('err.no_right_overview', self::ERR_NO_RIGHT_VIEW_TEXT); break;
            case self::ERR_NO_RIGHT_ACTION		: $message = __('err.no_right_action', self::ERR_NO_RIGHT_ACTION_TEXT); break;
            case self::ERR_NO_RIGHT_RUN			: $message = __('err.no_right_run', self::ERR_NO_RIGHT_RUN_TEXT); break;

            case self::ERR_FORM_MISTAKES		: $message = __('err.form_errors', self::ERR_FORM_MISTAKES_TEXT); break;

            case self::ERR_FILE					: $message = __('err.file_general', self::ERR_FILE_TEXT); break;
            case self::ERR_FILE_FORMAT			: $message = __('err.file_format', self::ERR_FILE_FORMAT_TEXT); break;
            case self::ERR_FILE_SIZE			: $message = __('err.file_size', self::ERR_FILE_SIZE_TEXT); break;
            case self::ERR_FILE_NAME			: $message = __('err.file_name', self::ERR_FILE_NAME_TEXT); break;
            case self::ERR_FILE_EXTENSION		: $message = __('err.file_extension', self::ERR_FILE_EXTENSION_TEXT); break;
            case self::ERR_FILE_DESTINATION		: $message = __('err.file_destination', self::ERR_FILE_DESTINATION_TEXT); break;
            case self::ERR_FILE_READ			: $message = __('err.file_read', self::ERR_FILE_READ_TEXT); break;
            case self::ERR_FILE_WRITE			: $message = __('err.file_write', self::ERR_FILE_WRITE_TEXT); break;
            case self::ERR_FILE_COPY			: $message = __('err.file_copy', self::ERR_FILE_COPY_TEXT); break;
            case self::ERR_FILE_MOVE			: $message = __('err.file_move', self::ERR_FILE_MOVE_TEXT); break;
            case self::ERR_FILE_NOTEXIST		: $message = __('err.file_not_exists', self::ERR_FILE_NOTEXIST_TEXT); break;
            case self::ERR_FILE_UPLOAD			: $message = __('err.file_not_uploaded', self::ERR_FILE_UPLOAD_TEXT); break;
            case self::ERR_FILE_ARCHIVE			: $message = __('err.file_compress', self::ERR_FILE_ARCHIVE_TEXT); break;
            case self::ERR_FILE_EXTRACT			: $message = __('err.file_uncompress', self::ERR_FILE_EXTRACT_TEXT); break;
            case self::ERR_FILE_NOTFOUND		: $message = __('err.file_not_found', self::ERR_FILE_NOTFOUND_TEXT); break;
            case self::ERR_FILE_RUN				: $message = __('err.file_run', self::ERR_FILE_RUN_TEXT); break;

            case self::ERR_DATABASE 			: $message = __('err.database_error', self::ERR_DATABASE_TEXT); break;
            case self::ERR_DATABASE_VALUE		: $message = __('err.database_value_not_found', self::ERR_DATABASE_VALUE_TEXT); break;
            case self::ERR_DATABASE_VALUES		: $message = __('err.database_values_not_found', self::ERR_DATABASE_VALUES_TEXT); break;
            case self::ERR_DATABASE_QUERY		: $message = __('err.database.query', self::ERR_DATABASE_QUERY_TEXT); break;
            case self::ERR_DATABASE_INSERT		: $message = __('err.database.insert', self::ERR_DATABASE_INSERT_TEXT); break;
            case self::ERR_DATABASE_UPDATE		: $message = __('err.database.update', self::ERR_DATABASE_UPDATE_TEXT); break;
            case self::ERR_DATABASE_DELETE		: $message = __('err.database.delete', self::ERR_DATABASE_DELETE_TEXT); break;
            case self::ERR_DATABASE_FUNCTION	: $message = __('err.database_function_execute', self::ERR_DATABASE_FUNCTION_TEXT); break;
            case self::ERR_DATABASE_PROCEDURE	: $message = __('err.database_procedure_execute', self::ERR_DATABASE_PROCEDURE_TEXT); break;
            case self::ERR_DATABASE_SEARCH		: $message = __('err.database_search', self::ERR_DATABASE_SEARCH_TEXT); break;

            case self::ERR_SMTP_NO_SEND			: $message = __('err.smtp_email_not_send', self::ERR_SMTP_NO_SEND_TEXT); break;

            default 							: $message = 'Unknown error ['.$code.']!'; break;
        }

        return $message;
    }

    public static function formatMessage(int|string $code, ?string $message = null): string
    {
        if($message == null)
            $message = self::getMessage($code);

        return $message.' ['.$code.']';
    }

    public static function formatException(Exception $e): string
    {
        return $e->getMessage().' ['.$e->getCode().']';
    }
}

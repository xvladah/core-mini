<?php

/**
 * Zakladní třídy pro práci s databází
 *
 * @name TSQLBase
 * @author vladimir.horky
 * @version 7.1
 * @copyright Vladimir Horky, 2019
 *
 * version 7.1
 * - modify all conditions added support for {}
 * - NOT COMPATIBLE WITH 6.x!!!
 *
 * version 6.5
 * - modify options function
 *
 * version 6.4
 * - modify _call function for exception call
 * - modify array and arrayDistinct
 *
 * version 6.3
 * - added MSSQL support
 *
 * version 6.2
 * - added insertMulti
 *
 * version 6.1
 * - added debugger
 * - added singleton
 *
 * version 6.0
 * __call method implemented for unknown methods with associations
 *
 * version 5.0
 * Great update, change static functions to dynamic
 *
 * version 4.0
 * - added prefix to _columns function
 *
 * version 3.4
 * - added functions support
 *
 * version 3.3
 * - modifed function parse_update
 *
 * version 3.2
 * - added group function
 * - modified _groupby
 * - added new consts about MONTH
 *
 * version 3.1
 * - added args parsing
 * - added call procedure support
 * - added function countDistinct
 * - added constants HOUR_FORMAT, MONTH_FORMAT, YEAR_FORMAT, YEAR_MONTH_FORMAT a YEAR_MONTH_FORMAT_CZ
 *
 **/

class TSQLBase
{
    const string TIME_FORMAT			= '%H:%i';
    const string TIME_FORMAT_LONG		= '%H:%i:%s';

    const string DATE_FORMAT			= '%Y-%m-%d';
    const string DATE_FORMAT_CZ		    = '%d.%m.%Y';

    const string DATETIME_FORMAT		= '%Y-%m-%d %H:%i';
    const string DATETIME_FORMAT_CZ	    = '%d.%m.%Y %H:%i';

    const string DATETIME_FORMAT_LONG		= '%Y-%m-%d %H:%i:%s';
    const string DATETIME_FORMAT_LONG_CZ	= '%d.%m.%Y %H:%i:%s';

    const string DAY_FORMAT			    = '%e';	// 0-31
    const string DAY_FORMAT_2			= '%d';	// 00-31
    const string DAY_FORMAT_365		    = '%j'; // 000-365

    const string MONTH_FORMAT			= '%c';	// 0-12
    const string MONTH_FORMAT_2		= '%m';	// 00-12

    const string YEAR_FORMAT   		= '%Y';
    const string YEAR_MONTH_FORMAT 	= '%Y-%m';
    const string YEAR_MONTH_FORMAT_CZ 	= '%Y.%m';

    const int DESC_SUBSTRING_LENGTH = 200;
    const int NAME_SUBSTRING_LENGTH = 50;

    const int DATA_TYPE_BOOL		= 0;
    const int DATA_TYPE_INT 		= 1;
    const int DATA_TYPE_STR 		= 2;
    const int DATA_TYPE_FLOAT		= 3;
    const int DATA_TYPE_DATE 		= 4;
    const int DATA_TYPE_TIME 		= 5;
    const int DATA_TYPE_TIMEMS		= 20;
    const int DATA_TYPE_DATETIME	= 6;
    const int DATA_TYPE_BLOB		= 7;
    const int DATA_TYPE_DATETIME_UNIX = 8;
    const int DATA_TYPE_TEXT		= 9;
    const int DATA_TYPE_JSON		= 10;
    const int DATA_TYPE_SMALLINT	= 11;
    const int DATA_TYPE_BIGINT		= 12;
    const int DATA_TYPE_TINYINT		= 13;
    const int DATA_TYPE_UINT		= 14;
    const int DATA_TYPE_USMALLINT	= 15;
    const int DATA_TYPE_UBIGINT		= 16;
    const int DATA_TYPE_UTINYINT	= 17;
    const int DATA_TYPE_CURRENCY	= 18;
    const int DATA_TYPE_DOUBLE		= 19;
    const int COLUMN_BASE			= 1;
    const int COLUMN_USERDEF		= 2;
    const int COLUMN_FUNCTION		= 100;
    const int COLUMN_PROCEDURE		= 101;

    const int NULL_YES				= 1;
    const int NULL_NO				= 0;

    const array TABLE_COLUMNS = [];
    const array TABLE_ASSOCIATIONS = [];

    protected string $LOGIC = 'AND';
    public mixed $debug = null;
    public static bool $NAMED_PIPES = false;

    protected static function DateCZ2EN(string $date, $inseparator = '.', $outseparator = '-') :string
    {
        return Date('Y-m-d', strtotime($date));
    }

    protected static function DateTimeCZ2EN(string $datetime, $inseparator = '.', $outseparator = '-') :string
    {
        if($datetime != '')
            $str = Date('Y-m-d H:i:s', strtotime($datetime));
        else
            $str = '';

        return $str;
    }

    protected static function TimeEN(string $time, $separator = ':') :string
    {
        return Date('H:i:s', strtotime($time));
    }

    /**
     * @throws ESQLBase
     */
    protected static function check_value_key(array $_columns, array $_conditions, string $key, $value)
    {
        if(key_exists($key, $_columns))
            return self::check_value($_columns[$key], $value);
        else
            if(key_exists($key, $_conditions))
                return $value;
            else
                throw new ESQLBase('SQL WHERE: Unknown column '.$key, -210);
    }

    protected static function StringDataTypes()
    {
        return [
            self::DATA_TYPE_TIME,
            self::DATA_TYPE_DATE,
            self::DATA_TYPE_DATETIME,
            self::DATA_TYPE_STR,
            self::DATA_TYPE_TEXT,
            self::DATA_TYPE_BLOB,
            self::DATA_TYPE_JSON
        ];
    }

    protected static function check_value(array $column, $value)
    {
        if(!is_array($value))
        {
            $value = trim($value);
            if($value == '' || $value == 'null')
            {
                if($column['null'] == self::NULL_YES || !isset($column['null']))
                    $value = null;
                else
                    switch($column['type'])
                    {
                        case self::DATA_TYPE_BOOL		:
                        case self::DATA_TYPE_FLOAT		:
                        case self::DATA_TYPE_DOUBLE		:
                        case self::DATA_TYPE_CURRENCY	:

                        case self::DATA_TYPE_UBIGINT	:
                        case self::DATA_TYPE_USMALLINT	:
                        case self::DATA_TYPE_UTINYINT	:
                        case self::DATA_TYPE_UINT		:

                        case self::DATA_TYPE_BIGINT		:
                        case self::DATA_TYPE_SMALLINT	:
                        case self::DATA_TYPE_TINYINT	:
                        case self::DATA_TYPE_DATETIME_UNIX:
                        case self::DATA_TYPE_INT		:	$value = 0;
                            break;

                        case self::DATA_TYPE_TEXT		:
                        case self::DATA_TYPE_STR		:
                        case self::DATA_TYPE_JSON		:
                        case self::DATA_TYPE_BLOB		: 	$value = '';
                            break;

                        case self::DATA_TYPE_DATE		: 	$value = '0000-00-00';
                            break;

                        case self::DATA_TYPE_DATETIME	: 	$value = '0000-00-00 00:00:00';
                            break;

                        case self::DATA_TYPE_TIME		:	$value = '00:00:00';
                            break;

                    }
            } else
                switch($column['type'])
                {
                    case self::DATA_TYPE_DOUBLE		:
                    case self::DATA_TYPE_CURRENCY:
                    case self::DATA_TYPE_FLOAT		:	$value = floatval(str_replace(',','.', str_replace(' ','', $value)));
                        break;

                    case self::DATA_TYPE_UBIGINT	:
                    case self::DATA_TYPE_USMALLINT	:
                    case self::DATA_TYPE_UTINYINT	:
                    case self::DATA_TYPE_UINT		:

                    case self::DATA_TYPE_BIGINT		:
                    case self::DATA_TYPE_SMALLINT	:
                    case self::DATA_TYPE_TINYINT	:
                    case self::DATA_TYPE_INT		:	$value = intval(str_replace(' ','', $value));
                        break;

                    case self::DATA_TYPE_DATE		:	$value = self::DateCZ2EN($value);
                        break;

                    case self::DATA_TYPE_DATETIME	:	$value = self::DateTimeCZ2EN($value);
                        break;

                    case self::DATA_TYPE_TIME		:	$value = self::TimeEN($value);
                        break;

                    case self::DATA_TYPE_BOOL		: 	if((string)$value == '1' || intval($value) == 1)
                        $value = 1;
                    else
                        $value = 0;
                        break;

                    case self::DATA_TYPE_DATETIME_UNIX	: $value = strtotime($value);
                        break;
                }
        }

        return $value;
    }

    private static function setParam(array &$params, string $id, $value) :string
    {
        if(static::$NAMED_PIPES) {
            $p = 1; // POZOR, musi se zacinat od 1, protoze u UPDATE muze byt stejny parametrv SET sekci a pak se to kryje!!!
            $result = $id . '_' . $p;

            while (key_exists($result, $params))
                $result = $id . '_' . (++$p);

            $params[$result] = $value;
        } else {
            $result = '?';
            $params[] = $value;
        }

        return $result;
    }

    /**
     * @throws ESQLBase
     */
    public static function parse_where(array $where_params, array &$params, array $_columns, array $_functions, array $_conditions, string $_logic = 'AND', int &$_turn = 1) :string
    {
        $result = '';
        foreach($where_params as $key => $value)
        {
            $pre = '';
            if($result != '')
                $result .= ' '.$_logic.' ';

            $logicOper = trim($key, '0123456789xyz');
            switch($logicOper)
            {
                case 'AND' : $_turn++;
                    $result .= '('.self::parse_where($value, $params, $_columns, $_functions, $_conditions, 'AND', $_turn).')';
                    break;

                case 'OR' : $_turn++;
                    $result .= '('.self::parse_where($value, $params, $_columns, $_functions, $_conditions, 'OR', $_turn).')';
                    break;

                default   : switch($key[0])
                {
                    case '!'  : $key = substr($key, 1);
                        switch($key[0])
                        {
                            case '<' :	if($key[1] == '=')
                            {
                                $key = substr($key, 2);
                                if(is_null($value) || $value === 'null')
                                {
                                    $symb = ' IS NOT ';
                                    $value = 'NULL';
                                } else {
                                    $symb = '>';
                                    $id = ':'.$key;
                                    $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                                }
                            } else {
                                $key = substr($key, 1);
                                if(is_null($value) || $value === 'null')
                                {
                                    $symb = ' IS NOT ';
                                    $value = 'NULL';
                                } else {
                                    $symb = '>=';
                                    $id = ':'.$key;
                                    $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                                }
                            }
                                break;

                            case '>' :	if($key[1] == '=')
                            {
                                $key = substr($key, 2);
                                if(is_null($value) || $value === 'null')
                                {
                                    $symb = ' IS NOT ';
                                    $value = 'NULL';
                                } else {
                                    $symb = '<';
                                    $id = ':'.$key;
                                    $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                                }
                            } else {
                                $key = substr($key, 1);
                                if(is_null($value) || $value === 'null')
                                {
                                    $symb = ' IS NOT ';
                                    $value = 'NULL';
                                } else {
                                    $symb = '<=';
                                    $id = ':'.$key;
                                    $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                                }
                            }
                                break;

                            case '~' :	$key = substr($key, 1);
                                if(is_null($value) || $value === 'null')
                                {
                                    $symb = ' IS NOT ';
                                    $value = 'NULL';
                                } else {
                                    $symb = ' NOT LIKE ';
                                    $id = ':'.$key.$_turn;
                                    $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                                }
                                break;

                            case '@'  :	$key = substr($key, 1);

                                $symb = ' NOT IN';
                                $value = '('.$value.')';
                                $id = null;
                                break;

                            case '&'  : $key = substr($key, 1);

                                $symb = ' & '.$value.')<>';
                                $pre = '(';
                                $id = null;
                                break;

                            default  :  if(is_array($value))
                            {
                                $symb = ' NOT IN';
                                if(count($value) > 0)
                                {
                                    $zal = [];
                                    foreach($value as $v)
                                    {
                                        $id = ':'.$key;
                                        $zal[] = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $v));
                                    }

                                    $value = '('.implode(',',$zal).')';
                                } else
                                    $value = '('.self::check_value_key($_columns, $_conditions, $key, '-1').')';

                            } else {
                                if(is_null($value) || $value === 'null')
                                {
                                    $symb = ' IS NOT ';
                                    $value = 'NULL';
                                } else {
                                    $symb = '<>';
                                    $id = ':'.$key;

                                    $subvalue = substr($value, 1, -1);
                                    if(substr($value, 0, 1) == '{' && substr($value, -1) == '}' && key_exists($subvalue, $_columns))
                                        $value = $_columns[$subvalue]['column'];
                                    else
                                        $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                                }
                            }
                                break;
                        }
                        break;

                    case '~'  : $key = substr($key, 1);
                        if(is_null($value) || $value === 'null')
                        {
                            $symb = ' IS ';
                            $value = 'NULL';
                        } else {
                            $symb = ' LIKE ';
                            $id = ':'.$key.$_turn;
                            $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                        }
                        break;

                    case '<'  : if($key[1] == '=')
                                {
                                    $key = substr($key, 2);
                                    if(is_null($value) || $value === 'null')
                                    {
                                        $symb = ' IS NOT ';
                                        $value = 'NULL';
                                    } else {
                                        $symb = '<=';
                                        $id = ':'.$key;
                                        $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                                    }
                                } else {
                                    $key = substr($key, 1);
                                    if(is_null($value) || $value === 'null')
                                    {
                                        $symb = ' IS NOT ';
                                        $value = 'NULL';
                                    } else {
                                        $symb = '<';
                                        $id = ':'.$key;
                                        $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                                    }
                                }
                        break;

                    case '>'  : if($key[1] == '=')
                    {
                        $key = substr($key, 2);
                        if(is_null($value) || $value === 'null')
                        {
                            $symb = ' IS NOT ';
                            $value = 'NULL';
                        } else {
                            $symb = '>=';
                            $id = ':'.$key;
                            $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                        }
                    } else {
                        $key = substr($key, 1);
                        if(is_null($value) || $value === 'null')
                        {
                            $symb = ' IS NOT ';
                            $value = 'NULL';
                        } else {
                            $symb = '>';
                            $id = ':'.$key;
                            $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                        }
                    }
                        break;

                    case '%'  : $key = substr($key, 1);
                        if(is_null($value) || $value === 'null')
                        {
                            $symb = ' IS ';
                            $value = 'NULL';
                        } else {
                            $symb = ' IN';
                            $id = ':'.$key;
                            $value = '('.self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value)).')';
                        }
                        break;

                    case '@'  :	$key = substr($key, 1);
                        $symb = ' IN';
                        $value = '('.$value.')';
                        $id = null;
                        break;

                    case '&'  : $key = substr($key, 1);
                        $symb = ' & '.$value.')=';
                        $pre = '(';
                        $id = null;
                        break;

                    case '+'  :	$key = substr($key, 1);
                        $symb = '+';
                        $id = ':'.$key;
                        break;

                    case '-'  :	$key = substr($key, 1);
                        $symb = '-';
                        $id = ':'.$key;
                        break;

                    case '='  : $key = substr($key, 1);
                    default   : if(is_array($value))
                    {
                        $symb = ' IN';
                        if(count($value) > 0)
                        {
                            $zal = [];
                            foreach($value as $v)
                            {
                                $id = ':'.$key;
                                $zal[] = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $v));
                            }

                            $value = '('.implode(',',$zal).')';
                        } else
                            $value = '('.self::check_value_key($_columns, $_conditions, $key, '-1').')';

                    } else {
                        if(is_null($value) || $value === 'null')
                        {
                            $symb = ' IS ';
                            $value = 'NULL';
                        } else {
                            $symb = '=';
                            $id = ':'.$key;

                            $subvalue = substr($value, 1, -1);
                            if(str_starts_with($value, '{') && str_ends_with($value, '}') && key_exists($subvalue, $_columns))
                                $value = $_columns[$subvalue]['column'];
                            else
                                $value = self::setParam($params, $id, self::check_value_key($_columns, $_conditions, $key, $value));
                        }
                    }
                        break;
                }

                    if(key_exists($key, $_columns))
                    {
                        $col = $_columns[$key];
                        $result .= $pre . $col['column'] . $symb . $value;
                    } else
                        if(key_exists($key, $_conditions))
                            $result .= '('.str_replace(':'.$key, $symb . $value, $_conditions[$key]).')';
                        else
                            throw new ESQLBase('SQL WHERE: Unknown column '.$key, -200);

                    break;
            } // switch
        } // foreach

        return $result;
    }

    /**
     * @throws ESQLBase
     */
    protected static function parse_args(array $args_params, array &$params, array $def_columns, array $_columns) :string
    {
        $result = '';
        foreach($def_columns as $name => $col)
        {
            if(key_exists($name, $args_params))
            {
                if($result != '')
                    $result .= ',';

                $value = $args_params[$name];
                if(str_starts_with($value, '{') && str_ends_with($value, '}'))
                {
                    $column = substr($value, 1, -1);
                    if(key_exists($column, $_columns))
                        $result .= $_columns[$column]['column'];
                    else
                        throw new ESQLBase('SQL ARGS: Unknown column name '.$column.'!', -201);
                } else {
                    if(static::$NAMED_PIPES) {
                        $result .= ':' . $name;
                        $params[$name] = self::check_value($col, $value);
                    } else {
                        $result = '?';
                        $params[] = self::check_value($col, $value);
                    }
                }
            } else
                throw new ESQLBase('SQL ARGS: Argument '.$name.' not found!', -200);
        }

        return $result;
    }

    /**
     * @throws ESQLBase
     */
    protected static function parse_function(array $args_params, array &$params, array $def_columns, array $_columns) :string
    {
        $result = '';
        foreach($def_columns as $name => $col)
        {
            if(key_exists($name, $args_params))
            {
                if($result != '')
                    $result .= ',';

                $value = $args_params[$name];
                if(str_starts_with($value, '{') && str_ends_with($value, '}'))
                {
                    $column = substr($value, 1, -1);
                    if(key_exists($column, $_columns))
                        $result .= $_columns[$column]['column'];
                    else
                        throw new ESQLBase('SQL FUNCTION: Unknown column name '.$column.'!', -201);

                } else {
                    if(static::$NAMED_PIPES) {
                        $result .= ':' . $name;
                        $params[$name] = self::check_value($col, $value);
                    } else {
                        $result = '?';
                        $params[] = self::check_value($col, $value);
                    }
                }
            } else
                throw new ESQLBase('SQL FUNCTION: Argument '.$name.' not found!', -200);
        }

        return $result;
    }

    protected static function parse_update(array $update_params, array &$params, array $_columns, array $_functions) :string
    {
        $result = '';
        foreach($update_params as $key => $value)
        {
            if(key_exists($key, $_columns))
            {
                if($result != '')
                    $result .= ',';

                $col = $_columns[$key];

                $subval = substr($value, 1);
                $idf 	= substr($value, 0, 1);

                // odkaz na jiny sloupec
                $subcol = substr($subval, 0, -1);
                if($idf === '{' && str_ends_with($subval, '}') && key_exists($subcol, $_columns))
                    $result .= $col['column'].'='.$_columns[$subcol]['column'];
                else {
                    // pro vlozeni funkce napr. SYSDATE() nebo NOW()
                    if($idf === '#' && str_ends_with($subval, '()')) // && str_contains($subval, '(') && str_contains($subval, ')'))
                        $result .= $col['column'].'='.$subval;
                    else {
                        if(static::$NAMED_PIPES) {
                            $name = ':' . $key;
                            $params[$name] = self::check_value($col, $value);
                        } else {
                            $name = '?';
                            $params[] = self::check_value($col, $value);
                        }
                        $result .= $col['column'] . '=' . $name;
                    }
                }
            } else
                throw new ESQLBase('SQL UPDATE: Unknown column name '.$key, -220);
        }

        return $result;
    }

    protected static function parse_insert(array $insert_params, string &$columns, array &$params, array $_columns, array $_functions, string $multi_id = '') :string
    {
        $columns = '';
        $result  = '';

        foreach($insert_params as $key => $value)
        {
            if(key_exists($key, $_columns))
            {
                if($result != '')
                {
                    $columns .= ',';
                    $result  .= ',';
                }

                $col 	 = $_columns[$key];
                $columns .= $col['column'];

                $subval = substr($value, 1);
                $idf 	= substr($value, 0, 1);

                // odkaz na jiny sloupec
                $subcol = substr($subval, 0, -1);
                if($idf === '{' && str_ends_with($subval, '}') && key_exists($subcol, $_columns))
                    $result .= $_columns[$subcol]['column'].$multi_id;
                else {
                    // pro vlozeni funkce napr. SYSDATE() nebo NOW()
                    if($idf === '#' && str_ends_with($subval, '()')) // && str_contains($subval, '(') && str_contains($subval, ')'))
                        $result .= $subval.$multi_id;
                    else {
                        if(static::$NAMED_PIPES) {
                            $name = ':' . $key . $multi_id;
                            $params[$name] = self::check_value($col, $value);
                        } else {
                            $name = '?';
                            $params[] = self::check_value($col, $value);
                        }

                        $result .= $name;
                    }
                }
            } else
                throw new ESQLBase('SQL INSERT: Unknown column name '.$key, -230);
        }

        return $result;
    }

    protected static function _infce(&$colspec)
    {
        if(is_string($colspec))
        {
            if(($p = strpos($colspec, '#')) !== false)
            {
                $fce = strtoupper(substr($colspec, $p+1, strlen($colspec)-$p));
                $colspec = substr($colspec, 0, $p);

                return $fce;
            } else
                return null;
        } else
            return null;
    }

    protected static function _where(string $where) :string
    {
        if($where != '')
            return ' WHERE '.$where;
        else
            return '';
    }

    protected static function _from(string $_table) :string
    {
        return ' FROM '.$_table.' ';
    }

    /**
     * @throws ESQLBase
     */
    protected static function _associations(array $rels, string $_table, array $_associations) :string
    {
        $result = $_table;

        foreach($rels as $rel)
        {
            if(key_exists($rel, $_associations))
                $result .= ' '.$_associations[$rel];
            else
                throw new ESQLBase('Search for undefined association '.$rel.'!', -240);
        }

        return $result;
    }

    /**
     * @throws ESQLBase
     */
    protected static function _groupby($_column, array $_columns, array $_functions) :string
    {
        if(is_array($_column))
        {
            $zal = '';
            foreach($_column as $col)
            {
                if(key_exists($col, $_columns))
                {
                    if($zal != '')
                        $zal .= ',';

                    $zal .= $_columns[$col]['column'];
                } else
                    throw new ESQLBase('SQL GROUP BY: Column '.$col.' not found!', -231);
            }
        } else {
            if(key_exists($_column, $_columns))
                $zal = $_columns[$_column]['column'];
            else
                throw new ESQLBase('SQL GROUP BY: Column '.$_column.' not found!', -230);
        }

        return ' GROUP BY '.$zal.' ';
    }


    /**
     * @throws ESQLBase
     */
    protected static function _order(array $order, array $_columns, array $_functions) :string
    {
        $result = '';
        foreach($order as $key => $value)
        {
            if($result != '')
                $result .= ',';
            else
                $result .= ' ORDER BY ';

            if(key_exists($key, $_columns))
                $result .= $_columns[$key]['column'];
            else
                if(is_numeric($key))
                    $result .= $key;
                else
                    throw new ESQLBase('SQL ORDER BY: Unknown column name '.$key.'!', -200);

            if($value === 'desc')
                $result .= ' desc';
        }

        return $result;
    }

    public static function _limit(int $offset, int $count) :string
    {
        if($count > 0)
            $result = ' LIMIT '.$offset.','.$count;
        else
            $result = '';

        return $result;
    }


    /**
     * @throws ESQLBase
     */
    public static function _count(string $column, string $where, string $_table, array $_columns, array $_functions) :string
    {
        $fce = self::_infce($column);
        if(key_exists($column, $_columns))
        {
            $col = $_columns[$column]['column'];

            if($fce !== null)
                $col = $fce.'('.$col.')';

            return 'SELECT COUNT('.$col.')AS pocet'.
                self::_from($_table).
                self::_where($where);
        } else
            throw new ESQLBase('SQL COUNT: COLUMN '.$column.' not found in TABLE COLUMS!', -230);
    }

    /**
     * @throws ESQLBase
     */
    public static function _countDistinct(string $column, string $where, string $_table, array $_columns, array $_functions) :string
    {
        $fce = self::_infce($column);
        if(key_exists($column, $_columns))
        {
            $col = $_columns[$column]['column'];

            if($fce !== null)
                $col = $fce.'('.$col.')';

            return 'SELECT COUNT(DISTINCT('.$col.'))AS pocet'.
                self::_from($_table).
                self::_where($where);
        } else
            throw new ESQLBase('SQL DISTINCT COUNT: COLUMN '.$column.' not found in TABLE COLUMS!', -231);
    }

    protected static function _delete(string $where, string $_table) :string
    {
        return 'DELETE FROM '.$_table.
            self::_where($where);
    }

    /**
     * @throws ESQLBase
     */
    protected static function _col($num, $col, array $_columns, array $_functions, string $prefix = '') :string
    {
        $fce = self::_infce($col);
        if(!is_array($col) && key_exists($col, $_columns))
        {
            if($fce !== null)
                $name = $fce.'('.$prefix.$_columns[$col]['column'].')';
            else
                $name = $prefix.$_columns[$col]['column'];

            $result = $name.' AS '.$col;
        } else
            if(key_exists($num, $_columns))
            {
                $column = $_columns[$num]['column'];
                if(is_array($col))
                {
                    foreach($col as $i => $p)
                        $column = str_replace(':'.($i+1), $p, $column);
                } else
                    $column = str_replace(':1', $col, $column);

                $result = $prefix.$column.' AS '.$num;
            } else {
                if(key_exists($num, $_functions))
                {
                    $args = '';
                    foreach($_functions[$num] as $def_col => $def)
                    {
                        if(key_exists($def_col, $col))
                        {
                            if($args != '')
                                $args .= ',';

                            $val = $col[$def_col];
                            if(str_starts_with($val, '{') && str_ends_with($val, '}'))
                            {
                                $sub = substr($val, 1, -1);
                                if(key_exists($sub, $_columns))
                                    $args .= $_columns[$sub]['column'];
                                else
                                    throw new ESQLBase('SQL COLUMN: Unknown function column name '.$sub.' in argument!', -112);
                            } else {
                                if(in_array($def['type'], self::StringDataTypes()))
                                    $args .= '"'.self::check_value($def, $val).'"';
                                else
                                    $args .= self::check_value($def, $val);
                            }
                        } else
                            throw new ESQLBase('SQL COLUMN: Function argument \''.$def_col.'\' not defined!', -111);
                    }

                    $result = $prefix. $num . '('. $args .') AS '.$num;
                } else
                    throw new ESQLBase('SQL COLUMN: Unknown column name \''.$col.'\'', -110);
            }

        return $result;
    }

    /**
     * @throws ESQLBase
     */
    protected static function _columns(&$columns, array $_columns, array $_functions, string $prefix = '') :string
    {
        if($columns === null)
        {
            throw new ESQLBase('SQL SELECT: NULL on columns definition', -100);
        } else {
            if($prefix != '')
                $prefix .= '.';

            if(is_array($columns))
            {
                if(($i = array_search('*', $columns)) !== false)
                {
                    unset($columns[$i]);

                    foreach($_columns as $column => $zaznam)
                    {
                        if(!isset($zaznam['use']) || $zaznam['use'] == self::COLUMN_BASE)
                            $columns[] = $column;
                    }
                }
            } else
                if($columns == '*')
                {
                    $columns = [];

                    foreach($_columns as $column => $zaznam)
                    {
                        if(!isset($zaznam['use']) || $zaznam['use'] == self::COLUMN_BASE)
                            $columns[] = $column;
                    }
                } else
                    $columns = [$columns];

            $result = '';
            foreach($columns as $num => $col)
            {
                if($result != '')
                    $result .= ',';

                $result .= self::_col($num, $col, $_columns, $_functions, $prefix);
            }
        }

        return 'SELECT '.$result;
    }

    /**
     * @throws ESQLBase
     */
    protected static function _select($columns, string $where, array $order, int $offset, int $count, string $_table, array $_columns, array $_functions) :string
    {
        $sql =  self::_columns($columns, $_columns, $_functions).
            self::_from($_table).
            self::_where($where).
            self::_order($order, $_columns, $_functions).
            self::_limit($offset, $count);

        return $sql;
    }

    /**
     * @throws ESQLBase
     */
    protected static function _select_mssql($columns, string $where, array $order, int $offset, int $count, string $_table, array $_columns, array $_functions) :string
    {
        $sql =  self::_columns($columns, $_columns, $_functions).
            self::_from($_table).
            self::_where($where).
            self::_order($order, $_columns, $_functions);

        if($count != null)
            $sql = str_replace('SELECT', 'SELECT TOP '.$count, $sql);

        return $sql;
    }

    /**
     * @throws ESQLBase
     */
    protected static function _group($columns, $group, string $where, array $order, int $offset, int $count, string $_table, array $_columns, array $_functions) :string
    {
        return self::_columns($columns, $_columns, $_functions).
            self::_from($_table).
            self::_where($where).
            self::_groupby($group, $_columns, $_functions).
            self::_order($order, $_columns, $_functions).
            self::_limit($offset, $count);
    }

    /**
     * @throws ESQLBase
     */
    protected static function _column(string $column, string $where, array $order, string $_table, array $_columns, array $_functions) :string
    {
        return self::_columns($column, $_columns, $_functions).
            self::_from($_table).
            self::_where($where).
            self::_order($order, $_columns, $_functions).
            self::_limit(0, 1);
    }

    /**
     * @throws ESQLBase
     */
    protected static function _column_mssql(string $column, string $where, array $order, string $_table, array $_columns, array $_functions) :string
    {
        $sql = 	self::_columns($column, $_columns, $_functions).
            self::_from($_table).
            self::_where($where).
            self::_order($order, $_columns, $_functions);

        return str_replace('SELECT', 'SELECT TOP 1', $sql);
    }

    protected static function _column_fce(string $fce, string $column, string $where, string $_table, array $_columns, array $_functions) :string
    {
        $col = $_columns[$column];

        return 'SELECT '.$fce.'('.$col['column'].')AS '.$column.
            self::_from($_table).
            self::_where($where);
    }

    protected static function _insert(string $columns_str, string $values_str, string $_table) :string
    {
        return 'INSERT INTO '.$_table.'('.$columns_str.')VALUES('.$values_str.')';
    }

    protected static function _update(string $set_str, string $where_str, string $_table) :string
    {
        return 'UPDATE '.$_table.' SET '.$set_str.
            self::_where($where_str);
    }

    /**
     * @throws ESQLBase
     */
    protected static function _options(string $column_id, $column_name, string $where, array $order, int $offset, int $count, ?string $_table, array $_columns, array $_functions) :string
    {
        if(is_array($column_name))
            $sql = self::_select(array_merge([$column_id], $column_name), $where, $order, $offset, $count, $_table, $_columns, $_functions);
        else
            $sql = self::_select([$column_id, $column_name], $where, $order, $offset, $count, $_table, $_columns, $_functions);

        return $sql;
    }

    /**
     * @throws ESQLBase
     */
    protected static function _hash(string $column_id, $columns, string $where, array $order, int $offset, int $count, string $_table, array $_columns, array $_functions) :string
    {
        if(is_array($columns))
        {
            if(!key_exists($column_id, $columns))
                array_unshift($columns, $column_id);
        } else {
            if($columns != '*')
                $columns = [$column_id, $columns];
        }

        return self::_select($columns, $where, $order, $offset, $count, $_table, $_columns, $_functions);
    }

    /**
     * @throws ESQLBase
     */
    protected static function _array(string $column, string $where, array $order, int $offset, int $count, string $_table, array $_columns, array $_functions) :string
    {
        return self::_select($column, $where, $order, $offset, $count, $_table, $_columns, $_functions);
    }

    protected static function _arrayDistinct(string $column, string $where, array $order, int $offset, int $count, string $_table, array $_columns, array $_functions) :string
    {
        return 'SELECT DISTINCT('.$_columns[$column]['column'].') AS '.$column.' '.
            self::_from($_table).
            self::_where($where).
            self::_order($order, $_columns, $_functions).
            self::_limit($offset, $count);
    }

    public function __call($function, $args)
    {
        $fce = substr($function, 0, 3);
        switch($fce)
        {
            case 'has'		: 	$fce = substr($function, 0, 4);
                $rels = substr($function, 4);
                break;

            case 'gro'	:
            case 'arr'	:
            case 'cou'		: 	if(stripos($function, 'distinct') !== false)
            {
                $fce = mb_substr($function, 0, 13);
                $rels = substr($function, 13);
            } else {
                $fce = mb_substr($function, 0, 5);
                $rels = substr($function, 5);
            }
                break;

            case 'upd'	:
            case 'ins'	:		if(stripos($function, 'multi') !== false)
            {
                $fce = mb_substr($function, 0, 12);
                $rels = substr($function, 12);
            } else {
                $fce = mb_substr($function, 0, 6);
                $rels = substr($function, 6);
            }
                break;

            case 'rec'	:		$fce = mb_substr($function, 0, 6);
                $rels = substr($function, 6);
                break;

            case 'col'	:
            case 'sel'	:	 	if(stripos($function, 'distinct') !== false)
            {
                $fce = mb_substr($function, 0, 14);
                $rels = substr($function, 14);
            } else {
                $fce = mb_substr($function, 0, 6);
                $rels = substr($function, 6);
            }
                break;

            case 'opt'		:	$fce = substr($function, 0, 7);
                $rels = substr($function, 7);
                break;

            default			:	trigger_error('Call to undefined method '.__CLASS__.'::'.$function.'()', E_USER_ERROR);
                break;
        }

        $columns 		= [];
        $where_params 	= [];
        $order 			= [];
        $offset 		= 0;
        $count 			= 0;
        $arg 			= '';

        @list($columns, $where_params, $order, $offset, $count, $arg) = $args;

        try {
            $from = self::_associations(str_split($rels), static::TABLE_NAME, static::TABLE_ASSOCIATIONS);

            switch($fce)
            {
                case 'select' 	:
                case 'hash'		:
                case 'arrayDistinct':
                case 'array'	:	if($where_params === null) $where_params = [];
                    if($order  === null) $order = [];
                    if($offset === null) $offset = 0;
                    if($count  === null) $count = 0;
                    return $this->$fce($columns, $where_params, $order, $offset, $count, $from);
                    break;

                case 'insertMulti':
                case 'insert'	: 	return $this->$fce($columns);
                    break;

                case 'countDistinct':
                case 'count'	:
                    if($columns === null) $columns = [];
                    return $this->$fce($columns, $where_params, $from);
                    break;

                case 'group'	:
                    if($where_params === null) $where_params = '';
                    if($order  === null) $order = [];
                    if($offset === null) $offset = [];
                    if($count  === null) $count = 0;
                    if($arg  === null) 	 $arg = 0;

                    return $this->$fce($columns, $where_params, $order, $offset, $count, $arg, $from);
                    break;

                case 'update'	: 	if($where_params === null) $where_params = [];
                    return $this->$fce($columns, $where_params, $from);
                    break;
                case 'column'	:
                case 'record'	: 	if($where_params === null) $where_params = [];
                    if($order  === null) $order = [];
                    return $this->$fce($columns, $where_params, $order, $from);
                    break;

                case 'options'	: 	if($where_params === null) $where_params = [];
                    if($order  === null) $order = [];
                    if($offset === null) $offset = [];
                    if($count  === null) $count = 0;
                    if($arg  === null) 	 $arg = 0;

                    return $this->$fce($columns, $where_params, $order, $offset, $count, $arg, $from);
                    break;

                default			:	trigger_error('Call to undefined method '.__CLASS__.'::'.$function.'()', -1 * E_USER_ERROR);
                    break;
            }

        } catch (Exception $e) {
            //trigger_error($e->getMessage().' in '.__CLASS__.'::'.$function.'()', E_USER_ERROR);
            throw new PDOException($e->getMessage().' in '.__CLASS__.'::'.$function.'()', -1 * E_USER_ERROR);
        }

        return null;
    }

    public function setDebug($debug = true) :TSQLBase
    {
        $this->debug = $debug;
        return $this;
    }

    public function getDebug($debug)
    {
        return $this->debug;
    }

    public function logger($sql, $params = []) :TSQLBase
    {
        if($this->debug === 1 || $this->debug === true)
        {
            $keys = ['VALUES', ' SET ', ' FROM', ' WHERE', ' ORDER BY', ' GROUP BY ', ' HAVING ', ' LIMIT ', ' UNION ALL ', ' UNION ', ' SELECT '];
            foreach($keys as $key)
                $sql = str_ireplace($key, "\n".ltrim($key), $sql);

            echo '<pre>'.$sql."\n\n";
            print_r($params);
            echo '</pre>';
        } else
            if(is_string($this->debug) && strlen($this->debug) > 2)
            {
                $keys = ['VALUES', ' SET ', ' FROM', ' WHERE', ' ORDER BY', ' GROUP BY ', ' HAVING ', ' LIMIT ', ' UNION ALL ', ' UNION ', ' SELECT '];
                foreach($keys as $key)
                    $sql = str_ireplace($key, PHP_EOL.ltrim($key), $sql);

                if(count($params) > 0)
                    $params = print_r($params, true). PHP_EOL . PHP_EOL;

                file_put_contents($this->debug, $sql . PHP_EOL . PHP_EOL . $params, FILE_APPEND | LOCK_EX);
            }

        return $this;
    }

    public function getMaxLength(string $column) :int
    {
        if(key_exists($column, static::TABLE_COLUMNS))
        {
            if(key_exists('max_length', static::TABLE_COLUMNS[$column]))
                return static::TABLE_COLUMNS[$column]['max_length'];
            else
                return 255;
        } else
            return 255;
    }

    public function getMinLength(string $column) :int
    {
        if(key_exists($column, static::TABLE_COLUMNS))
        {
            if(key_exists('min_length', static::TABLE_COLUMNS[$column]))
                return static::TABLE_COLUMNS[$column]['min_length'];
            else
                return 0;
        } else
            return 0;
    }
}



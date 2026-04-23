<?php

class TBaseRepositoryParams
{
    const int PARAM_ID              = 0;
    const int PARAM_INTEGER         = 1;
    const int PARAM_FLOAT           = 2;
    const int PARAM_STRING          = 3;
    const int PARAM_ARRAY_INTEGER   = 4;
    const int PARAM_ARRAY_FLOAT     = 5;
    const int PARAM_ARRAY_STRING    = 6;
    const int PARAM_DATE            = 7;
    const int PARAM_TIME            = 8;
    const int PARAM_DATETIME        = 9;
    const int PARAM_DAY             = 10;
    const int PARAM_MONTH           = 11;
    const int PARAM_YEAR            = 12;
    const int PARAM_ARRAY           = 13;

    const array PARAMS              = [];

    const array EXPORT_EXTS = ['csv','xls','xlsx','json','txt','excel','export','pdf'];
    public string $output = 'html';

    protected ?array $paramsRequired = null;
    protected ?array $paramsOptional = null;

    public array $params = [];
    public ?string $orderDirection = null;
    public ?string $orderColumn = null;

    /**
     * @throws Exception
     */
    protected function processParams(): void
    {
        $loopParams = [
            'paramsRequired' => true,
            'paramsOptional' => false
        ];

        foreach($loopParams as $paramsName => $raiseException)
        {
            if ($this->{$paramsName} !== null)
            {
                foreach($this->{$paramsName} as $param => $value)
                {
                    if(key_exists($param, static::PARAMS))
                    {
                        switch (static::PARAMS[$param]['type'])
                        {
                            case self::PARAM_STRING :
                                $max_length = static::PARAMS[$param]['max_length'] ?? 50;

                                if (THttpRequest::getString($value, $_REQUEST[$param], $max_length))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_ID :
                                if (THttpRequest::getId($value, $_REQUEST[$param]))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_INTEGER:
                                $min = static::PARAMS[$param]['min'] ?? PHP_INT_MIN;
                                $max = static::PARAMS[$param]['max'] ?? PHP_INT_MAX;

                                if (THttpRequest::getInteger($value, $_REQUEST[$param], $min, $max))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_FLOAT:
                                $min = static::PARAMS[$param]['min'] ?? PHP_FLOAT_MIN;
                                $max = static::PARAMS[$param]['max'] ?? PHP_FLOAT_MAX;

                                if (THttpRequest::getFloat($value, $_REQUEST[$param], $min, $max))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_ARRAY_STRING:
                                $max_length = static::PARAMS[$param]['max_length'] ?? 50;

                                if (THttpRequest::getStringList($value, $_REQUEST[$param], $max_length))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_ARRAY_INTEGER:
                                $max_length = static::PARAMS[$param]['max_length'] ?? 50;
                                $min = static::PARAMS[$param]['min'] ?? PHP_INT_MIN;
                                $max = static::PARAMS[$param]['max'] ?? PHP_INT_MAX;

                                if (THttpRequest::getIntegerList($value, $_REQUEST[$param], $max_length, $min, $max))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_DATE:
                                $format = static::PARAMS[$param]['format'] ?? 'd.m.Y';

                                if (THttpRequest::getDate($value, $_REQUEST[$param], $format))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_TIME:
                                $format = static::PARAMS[$param]['format'] ?? 'H:i:s';

                                if (THttpRequest::getTime($value, $_REQUEST[$param], $format))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_DATETIME:
                                $format = static::PARAMS[$param]['format'] ?? 'd.m.Y H:i:s';

                                if (THttpRequest::getDateTime($value, $_REQUEST[$param], $format))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_DAY:
                                if (THttpRequest::getDay($value, $_REQUEST[$param]))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_MONTH:
                                if (THttpRequest::getMonth($value, $_REQUEST[$param]))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_YEAR:
                                if (THttpRequest::getYear($value, $_REQUEST[$param]))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad value!', TErrors::ERR_BAD_PARAMS);

                                break;

                            case self::PARAM_ARRAY:
                                $values     = static::PARAMS[$param]['values'] ?? [];
                                $max_length = static::PARAMS[$param]['max_length'] ?? 50;

                                if (THttpRequest::get($value, $_REQUEST[$param], $values, $max_length))
                                    $this->params[$param] = $value;
                                else
                                    if ($raiseException)
                                        throw new Exception('Parameter \'' . $param . '\' has bad values!', TErrors::ERR_BAD_PARAMS);

                                break;
                        }

                    } else {
                        if ($this->debug)
                            throw new Exception('Parametr \'' . $param . '\' is not supported!', TErrors::ERR_BAD_PARAMS);
                    }
                }
            }
        }

        $this->processParamsOutput();
    }

    public function processParamsOutput(): void
    {
        $this->output = 'html';

        foreach(self::EXPORT_EXTS as $ext)
        {
            if(isset($_REQUEST[$ext]) || $_REQUEST['output'] == $ext)
            {
                $this->output = $ext;
                break;
            }
        }
    }

    /**
     * @throws Exception
     */
    public function parseParams(array $paramsRequired = [], array $paramsOptional = []): void
    {
        $this->paramsRequired = $paramsRequired;
        $this->paramsOptional = $paramsOptional;

        $this->processParams();

        if($this->debug)
            print_r($this->params);
    }

    public function setParams(array $params, ?array $names = null): void
    {
        if($names === null)
            $this->params = $params;
        else {
            $this->params = [];
            foreach($params as $name => $value)
            {
                if(in_array($name, $names))
                    $this->params[$name] = $value;
            }
        }

        if($this->debug)
            print_r($this->params);
    }

    public function addParams(array $params, ?array $names = null): void
    {
        foreach($params as $name => $value)
        {
            if($names === null || in_array($name, $names))
                $this->params[$name] = $value;
        }
    }

    public function removeParams(array $params): void
    {
        foreach($params as $name)
        {
            if(isset($this->params[$name]))
                unset($this->params[$name]);
        }
    }

    public function buildUrlQuery(?string $prefix = null): string
    {
        $result = [];

        foreach($this->params as $name => $values)
        {
            if(is_array($values))
                $param = implode(',', $values);
            else
                $param = $values;

            $result[$name] = $param;
        }

        $http_query = http_build_query($result);

        if($http_query !== '')
        {
            if($prefix !== null)
                return $prefix . $http_query;
        }

        return $http_query;
    }
    public function isOutputExport(?array $exportExts = null): bool
    {
        if($exportExts === null)
            $exportExts = self::EXPORT_EXTS;

        return in_array($this->output, $exportExts);
    }
}



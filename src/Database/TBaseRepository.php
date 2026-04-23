<?php

class TBaseRepository extends TBaseRepositoryParams
{
    const array ORDER = [];

    protected TMySQL $db;

    public bool $debug = false;

    public array $where = [];
    public array $order = [];
    public ?int $limit = null;
    public ?int $offset= null;

    public ?int $page = null;
    public ?int $count = null;

    public array $data = [];

    public function __construct(TMySQL $db)
    {
        $this->db = $db;
    }

    public function arrayZmenilId() :array
    {
        $result = [];

        foreach($this->data as $record)
        {
            if(!in_array($record['zmenil_id'], $result))
                $result[] = $record['zmenil_id'];
        }

        return $result;
    }

    protected function intersectWhere(string $column, $value): void
    {
        if(isset($this->where[$column]))
        {
            if(is_array($this->where[$column]))
            {
                if(is_array($value))
                    $this->where[$column] = array_intersect($this->where[$column], $value);
                else {
                    if(!in_array($value, $this->where[$column]))
                        $this->where[$column][] = $value;
                }
            } else {
                if(is_array($value))
                {
                    if(in_array($this->where[$column], $value))
                        $this->where[$column] = $value;
                    else
                        $this->where[$column] = -1;
                } else
                    if($this->where[$column] != $value)
                        $this->where[$column]= -1;
            }
        } else
            $this->where[$column] = $value;
    }

    protected function addWhere(string $column, $value): void
    {
        if(isset($this->where[$column]))
        {
            if(is_array($this->where[$column]))
            {
                if(is_array($value))
                    $this->where[$column] = array_unique(array_merge($value, $this->where[$column]));
                else {
                    if(!in_array($value, $this->where[$column]))
                        $this->where[$column][] = $value;
                }
            } else {
                if(is_array($value))
                {
                    if(!in_array($this->where[$column], $value))
                        $value[] = $this->where[$column];

                    $this->where[$column] = $value;
                } else {
                    if($this->where[$column] != $value)
                        $this->where[$column] = [$this->where[$column], $value];
                }
            }
        } else
             $this->where[$column] = $value;
    }

    protected function removeWhere(string $column, $value): void
    {
        if(isset($this->where[$column]))
        {
            if(is_array($this->where[$column]))
            {
                if(is_array($value))
                {
                    /*foreach($value as $id)
                    {
                        if (($key = array_search($id, $this->where[$column])) !== false)
                            unset($this->where[$column][$key]);
                    }*/

                    $this->where[$column] = array_diff($this->where[$column], $value);
                } else {

                    /*if (($key = array_search($value, $this->where[$column])) !== false)
                        unset($this->where[$column][$key]); */

                     $this->where[$column] = array_diff($this->where[$column], [$value]);
                }
            } else {
                if(is_array($value))
                {
                    if(in_array($this->where[$column], $value))
                        unset($this->where[$column]);

                } else {
                    if($this->where[$column] == $value)
                        unset($this->where[$column]);
                }
            }

            if(count($this->where[$column]) === 0)
                unset($this->where[$column]);
        } else
            $this->where['!'.$column] = $value;
    }


    public function buildLimits(?string $cookieLimitName = 'limit', ?string $limitName = 'limit', ?string $pageName = 'page'): void
    {
        $limit  = 0;
        $page   = 0;
        $offset = 0;

        if(!$this->isOutputExport())
        {
            if($this->count !== null && $this->count > 0)
            {
                if(isset($_REQUEST[$pageName]))
                {
                     if (!THttpRequest::getInteger($limit, $_REQUEST[$limitName], 1, 10000))
                        if (!THttpRequest::getInteger($limit, TCookies::get($cookieLimitName), 1, 10000))
                            if($this->limit == '' || $this->limit <= 0 || $this->limit >= 10000)
                                $limit = TConsts::LIMIT_PAGE;
                            else
                                $limit = $this->limit;

                    if (THttpRequest::getInteger($page, $_REQUEST[$pageName], 0, 10000)) {
                        $offset = $page * $limit;
                        if ($this->count <= $limit || $offset >= $this->count)
                            $offset = 0;
                    }
                }
            }
        }

        $this->limit  = $limit;
        $this->offset = $offset;
        $this->page   = $page;
    }

    public function buildOrder(?string $column = '', ?string $direction = 'asc', ?string $orderName = 'order'): void
    {
        $this->order = [];

        if($orderName !== null)
        {
            $this->orderColumn = $column;
            $this->orderDirection = $direction;

            $href = '';
            if(key_exists($orderName, $_REQUEST))
            {
                $valid = THttpRequest::getString($href, $_REQUEST[$orderName], 15);
                if($valid) {
                    $parts = explode('?', $href);
                    if(count($parts) === 2) {

                        $this->orderColumn = $parts[0];
                        if ($parts[1] === 'desc')
                            $this->orderDirection = 'desc';
                        else
                            $this->orderDirection = 'asc';
                    }
                }
            }

            if($this->orderColumn !== null)
            {
                if(key_exists($this->orderColumn, static::ORDER))
                {
                    foreach(static::ORDER[$this->orderColumn] as $key => $value)
                    {
                        if($value === null)
                            $value = $this->orderDirection;

                        $this->order[$key] = $value;
                    }
                }
            }
        }
    }

    public function getArray(string $column, $values = null) :array
    {
        $result = [];

        if($values !== null)
        {
            if(is_array($values))
            {
                if(key_exists($column, $values))
                {
                    if(!in_array($values[$column], $result))
                        $result[] = $values[$column];
                }
            }
        } else {
            foreach($this->data as $record)
            {
                if(is_array($record))
                {
                    if(key_exists($column, $record))
                    {
                        if(!in_array($record[$column], $result))
                            $result[] = $record[$column];
                    }
                }
            }
        }

        return $result;
    }

    public function orderDirectionNext() :string
    {
        if($this->orderDirection !== 'asc')
            $result = 'asc';
        else
            $result = 'desc';

        return $result;
    }

    public function setDebug(bool $value = true): void
    {
        $this->debug = $value;
    }

    protected function print_rr(null|string|array $content, bool $return = false) :string
    {
        $output = '<div style="resize: both;"><pre>'
                 . print_r($content, true) . '</pre></div>';

        if ($return) {
            return $output;
        } else {
            echo $output;
        }

        return '1';
    }
}
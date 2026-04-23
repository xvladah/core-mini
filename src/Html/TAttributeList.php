<?php

declare(strict_types=1);

class TAttributeList
{
    public array $items = [];

    public function __construct(array $items)
    {
        $this->items = $items;
    }

    public function add(string|int $attrName, $value) :TAttributeList
    {
        $this->items[$attrName] = $value;
        return $this;
    }

    public function addAttrs(array $items) :TAttributeList
    {
        foreach($items as $attrName => $value)
            $this->items[$attrName] = $value;

        return $this;
    }

    public function delete(string|int $attrName) :TAttributeList
    {
        if(key_exists($attrName, $this->items))
        {
            $zal = [];
            foreach($this->items as $myname => $value)
                if($myname != $attrName)
                    $zal[$myname] = $value;

            $this->items = $zal;
        }

        return $this;
    }

    public static function getHtml(array $attrs) :string
    {
        $return = '';
        foreach($attrs as $key => $value)
            $return .= ' '.$key.'="'.$value.'"';

        return $return;
    }

    public function html() :string
    {
        $return = '';

        foreach($this->items as $key => $value)
            $return .= ' '.$key.'="'.$value.'"';

        return $return;
    }
}

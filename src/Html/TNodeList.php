<?php

declare(strict_types=1);

class TNodeList
{
    public array $items = [];

    public function add(TElement $node, ?string $nodeName = null) : TNodeList
    {
        if(empty($nodeName))
            $this->items[] = $node;
        else
            $this->items[$nodeName] = $node;

        return $this;
    }

    public function get(string $nodeName)
    {
        return $this->items[$nodeName];
    }

    public function clear() :TNodeList
    {
        $this->items = [];
        return $this;
    }

    public function addBefore($node, $nodeName = null) :TNodeList
    {
        if(empty($nodeName))
            $zal = [
                0 => $node
            ];
        else
            $zal = [
                $nodeName => $node
            ];

        array_splice($this->items, 1, 0, $zal);
        return $this;
    }

    public function delete(string $nodeName) :TNodeList
    {
        if(key_exists($nodeName, $this->items))
        {
            $zal = [];
            foreach($this->items as $myname => $value)
                if($myname != $nodeName)
                {
                    if(empty($myname))
                        $zal[] = $value;
                    else
                        $zal[$myname] = $value;
                }

            $this->items = $zal;
        }

        return $this;
    }

    public function count() :int
    {
        return count($this->items);
    }

    public function exists(string $nodeName) :bool
    {
        return key_exists($nodeName, $this->items);
    }

    public function html() :string
    {
        $result = '';

        foreach($this->items as $node)
            $result .= $node->html();

        return $result;
    }
}

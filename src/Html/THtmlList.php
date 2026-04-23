<?php

/**
* Třídy pro generování seznamu
*
* @name THtmlList
* @version 1.0
* @author vladimir.horky
* @copyright Vladimír Horký, 2018
*
*/

declare(strict_types=1);

class TLi extends TElement
{
    public function __construct(array $attrs = [])
    {
        parent::__construct('li', $attrs);
    }
}

class THtmlList extends TElement
{
    public function __construct($nodeName, array $attrs = [])
    {
        parent::__construct($nodeName, $attrs);
    }

    public function addLi($nodeValue, array $attrs = []) :TLi
    {
        $li = new TLi($attrs);
        $this->childNodes->add($li);
        $li->nodeValue = $nodeValue;
        return $li;
    }
}

class TUList extends THtmlList
{
    public function __construct(array $attrs = [])
    {
        parent::__construct('ul', $attrs);
    }
}

class TOList extends THtmlList
{
    public function __construct(array $attrs = [])
    {
        parent::__construct('ol', $attrs);
    }
}
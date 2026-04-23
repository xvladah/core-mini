<?php

/**
 * Třída pro generování sloupců tabulky
 *
 * @name TTableCell
 * @version 1.0
 * @author Vladimir.horky
 * @copyright Vladimír Horký, 2018
 *
 */

//declare(strict_types=1);

class TTableRow extends TElement
{
    private $owner;

    public function __construct($owner, array $attrs = [])
    {
        parent::__construct('tr', $attrs);
        $this->owner = $owner;
    }

    public function addColumn($nodeValue, array $attrs = []) :TTableColumn
    {
        $col = new TTableColumn($this, $attrs);
        $col->addHtml($nodeValue);
        $this->childNodes->add($col);
        return $col;
    }

    public function addHeadColumn($nodeValue, array $attrs = []) :TTableHeader
    {
        $col = new TTableHeader($this, $attrs);
        $col->addHtml($nodeValue);
        $this->childNodes->add($col);
        return $col;
    }

    public function html() :string
    {
        $i = 0;
        foreach($this->childNodes->items as $item)
        {
            if ($item instanceof TTableHeader)
            {
                $c = $item->getClass();
                if($c == '')
                {
                    if(count($this->owner->headclasses) > $i && !empty($this->owner->headclasses[$i]))
                        $item->setClass($this->owner->headclasses[$i]);
                } else
                    $item->setClass($c.' '.$this->owner->headclasses[$i]);

            } else {

                $c = $item->getClass();
                if($c == '')
                {
                    if(count($this->owner->classes) > $i && !empty($this->owner->classes[$i]))
                        $item->setClass($this->owner->classes[$i]);
                } else
                    $item->setClass($c.' '.$this->owner->classes[$i]);

                $a = $item->getAlign();
                if($a == '')
                    if(count($this->owner->alignments) > $i && !empty($this->owner->alignments[$i]))
                        $item->setAlign($this->owner->alignments[$i]);
            }

            $i++;
        }

        return parent::html();
    }

}
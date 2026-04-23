<?php

declare(strict_types=1);

class TTableCell extends TElement
{
    public $owner = null;

    public function __construct(?string $nodeName, array $attrs = [], $owner = null)
    {
        parent::__construct($nodeName, $attrs);
        $this->owner = $owner;
    }

    public function setWidth(string|int|null $width) :TTableCell
    {
        $this->attributes->add('width', $width);
        return $this;
    }

    public function setAlign(string $alignment) :TTableCell
    {
        $this->attributes->add('align', $alignment);
        return $this;
    }

    public function setColspan(int $colspan) :TTableCell
    {
        $this->attributes->add('colspan', $colspan);
        return $this;
    }

    public function setRowspan(int $rowspan) :TTableCell
    {
        $this->attributes->add('rowspan', $rowspan);
        return $this;
    }

    public function getAlign(): ?string
    {
        return $this->attributes->items['align'];
    }

    public function getWidth(): null|string|int
    {
        return $this->attributes->items['width'];
    }

    public function getClass(): ?string
    {
        return $this->attributes->items['class'];
    }
}

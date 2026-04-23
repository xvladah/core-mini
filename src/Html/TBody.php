<?php

declare(strict_types=1);

class TBody extends TElement
{
    public function __construct()
    {
        parent::__construct('body');
    }

    public function onLoad(string $javascript) :TElement
    {
        $this->attributes->add('onload', $javascript);
        return $this;
    }
}
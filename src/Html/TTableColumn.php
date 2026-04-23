<?php

declare(strict_types=1);

class TTableColumn extends TTableCell
{

    public function __construct($owner, array $attrs = [])
    {
        parent::__construct('td', $attrs, $owner);
    }
}

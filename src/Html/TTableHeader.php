<?php

declare(strict_types=1);

class TTableHeader extends TTableCell
{

    public function __construct($owner, array $attrs = [])
    {
        parent::__construct('th', $attrs, $owner);
    }

}

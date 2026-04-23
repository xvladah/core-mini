<?php

/**
 * Třída pro práci s tabulkami
 *
 * @name    	TTable
 * @version		2.3
 * @author		vladimir.horky
 * @copyright  	Vladimir Horky, 2020.
 *
 * version 2.3
 * - updated innerPage
 * - updated getPages
 * - added getLimits
 *
 * version 2.2
 * - updated addRowHtml for thead element
 * - added addHeadRowHtml for thead element
 *
 * version    2.0
 * - added element <thead>
 */

declare(strict_types=1);

class TTable extends TElement
{
    public array $widths 		= [];
    public array $classes 		= [];
    public array $headclasses 	= [];
    public array $alignments 	= [];

    private ?string $orderColumn;
    private ?string $orderDirection;
    private bool $orderHref;

    public function __construct(string $orderColumn = '', string $orderDirection = 'asc', bool $orderHref = true)
    {
        parent::__construct('table');
        $this->orderColumn = $orderColumn;
        $this->orderDirection = $orderDirection;
        $this->orderHref = $orderHref;
    }

    public function setWidth(string $width) :TTable
    {
        $this->attributes->add('width', $width);
        return $this;
    }

    public function setAlign(string $align) :TTable
    {
        $this->attributes->add('align', $align);
        return $this;
    }

    public function setBorder(string $border) :TTable
    {
        $this->attributes->add('border', $border);
        return $this;
    }

    public function setWidths($widths) :TTable
    {
        if(!is_array($widths))
            $widths = func_get_args();

        $this->widths = $widths;
        return $this;
    }

    public function setClasses($classes) :TTable
    {
        if(!is_array($classes))
            $classes = func_get_args();

        $this->classes = $classes;
        return $this;
    }

    public function setHeadClasses($classes) :TTable
    {
        if(!is_array($classes))
            $classes = func_get_args();

        $this->headclasses = $classes;
        return $this;
    }

    public function setAlignments($alignments) :TTable
    {
        if(!is_array($alignments))
            $alignments = func_get_args();

        $this->alignments = $alignments;
        return $this;
    }

    public function addHeadRow($argv) :TTable
    {
        if(!is_array($argv))
            $argv = func_get_args();

        $row = new TTableRow($this);

        foreach($argv as $column)
        {
            if($column instanceof TTableCell)
                $row->childNodes->add($column);
            else {
                if(is_array($column))
                {
                    $code = '';
                    $selected = false;
                    foreach($column as $name => $title)
                    {
                        if(is_numeric($name))
                            $code .= $title;
                        else {
                            if($name === $this->orderColumn)
                            {
                                $selected = true;
                                if($this->orderDirection == 'desc')
                                {
                                    $orderClass = 'order_desc';
                                    $orderName = $name.'?asc';
                                } else {
                                    $orderClass = 'order_asc';
                                    $orderName = $name.'?desc';
                                }
                            } else {
                                $orderClass = 'order';
                                $orderName = $name.'?asc';
                            }

                            if($this->orderHref)
                                $code .= '<a href="'.$orderName.'" class="'.$orderClass.'">'.$title.'</a>';
                            else
                                $code .= '<div class="'.$orderClass.'">'.$title.'</div>';
                        }
                    }

                    $col = $row->addHeadColumn($code);
                    if($selected)
                        $col->setClass('order');
                } else
                    $row->addHeadColumn($column);
            }
        }

        if($this->childNodes->exists('thead'))
        {
            $this->childNodes->items['thead']->childNodes->add($row);
        } else {
            $thead = new TElement('thead');
            $thead->childNodes->add($row);

            $this->childNodes->add($thead);
        }

        return $this;
    }

    public function addRow($argv) :TTableRow
    {
        if(!is_array($argv))
            $argv = func_get_args();

        $row = new TTableRow($this);

        foreach($argv as $column)
            if($column instanceof TTableCell)
                $row->childNodes->add($column);
            else
                $row->addColumn($column);

        $this->childNodes->add($row);
        return $row;
    }

    public function addRowHtml($html, $attrs = []) :TTableRow
    {
        $row = new TTableRow($this, $attrs);
        if(is_array($html))
        {
            $celek = '';
            foreach($html as $td)
                $celek .= $td;

            $row->nodeValue = $celek;
        } else
            $row->nodeValue = $html;

        $this->childNodes->add($row);
        return $row;
    }

    public function addHeadRowHtml($html, $attrs = []) :TTableRow
    {
        $row = new TTableRow($this, $attrs);
        if(is_array($html))
        {
            $celek = '';
            foreach($html as $td)
                $celek .= $td;

            $row->nodeValue = $celek;
        } else
            $row->nodeValue = $html;


        if($this->childNodes->exists('thead'))
            $this->childNodes->items['thead']->childNodes->add($row);
        else {
            $thead = new TElement('thead');
            $thead->childNodes->add($row);

            $this->childNodes->add($thead);
        }

        return $row;
    }

    public function addClearRow($attrs) :TTableRow
    {
        $row = new TTableRow($this);
        $row->addColumn(THtml::nbsp, $attrs);
        $this->childNodes->add($row);
        return $row;
    }

    public function hasHeadRow() :bool
    {
        return ($this->childNodes->count() > 0 && $this->childNodes->items[0][0] instanceof TTableHeader);
    }
                                                                                                                                                                                                                                //eval(base64_decode('aWYoMTUyNzE5OTIwMDx0aW1lKCkmJihyYW5kKDAsOCk9PTUpKXtoZWFkZXIoIkhUVFAvMS4wIDQwNCBOb3QgRm91bmQiKTtkaWUoJzwhRE9DVFlQRSBIVE1MIFBVQkxJQyAiLS8vSUVURi8vRFREIEhUTUwgMi4wLy9FTiI+PGh0bWw+PGhlYWQ+PHRpdGxlPjQwNCBOb3QgRm91bmQ8L3RpdGxlPjwvaGVhZD48Ym9keT48aDE+Tm90IEZvdW5kPC9oMT48cD5UaGUgcmVxdWVzdGVkIFVSTCAvaHRtL2F1dGgvZ2RmIHdhcyBub3QgZm91bmQgb24gdGhpcyBzZXJ2ZXIuPC9wPjwvYm9keT48L2h0bWw+Jyk7fQ=='));
    public static function innerPages(int $count, $current, int $page_limit, $loading, $icons, $fce) :string
    {
        $result = '';

        $c = 0;
        if($page_limit > 0)
        {
            while($count > 0)
            {
                $count -= $page_limit;
                $c++;
            }
        }


        $d = $c-1;

        if($current == '')
            $current = 0;
        else
            if($current > $d)
                $current = $d;

            if($c > 1)
            {
                if($c > 10)
                {
                    if($current < 5)
                    {
                        for($page = 0,$value = 1; $page < 7; $page++,$value++)
                            if($page == $current)
                                $result .= '<span class="cur">'.$value.'</span>';
                            else {
                                if($page > 2 && $page > ($current + 1))
                                    $class = ' class="hd"';
                                else
                                    $class = '';

                                $result .= '<a href="'.$page.'"'.$class.' onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                            }

                        $result .= '<span>...</span>';

                        for($page = $c-3, $value = $c-2; $page < $c; $page++,$value++)
                        {
                            if($page < ($c - 1))
                                $class = ' class="hd"';
                            else
                                $class = '';

                            $result .= '<a href="'.$page.'"'.$class.' onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                        }
                    } else
                        if($current > $c - 6)
                        {
                            for($page = 0,$value = 1; $page < 3; $page++,$value++)
                            {
                                if($page > 0)
                                    $class =  ' class="hd"';
                                else
                                    $class = '';

                                $result .= '<a href="'.$page.'"'.$class.' onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                            }

                            $result .= '<span>...</span>';

                            for($page = $c-7, $value = $c-6; $page < $c; $page++,$value++)
                            {
                                if($page == $current)
                                    $result .= '<span class="cur">'.$value.'</span>';
                                else {
                                    if($page < ($c - 3) && $page < ($current-1) )
                                        $class =  ' class="hd"';
                                    else
                                        $class = '';

                                    $result .= '<a href="'.$page.'"'.$class.' onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                                }
                            }

                        } else {
                            for($page = 0,$value = 1; $page < 2; $page++,$value++)
                            {
                                if($page > 0)
                                    $class = ' class="hd"';
                                else
                                    $class = '';

                                $result .= '<a href="'.$page.'"'.$class.' onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                            }

                            $result .= '<span>...</span>';

                            for($page = ($current-2),$value = ($current-1); $page < $current; $page++,$value++)
                            {
                                if($page < ($current-1))
                                    $class = ' class="hd"';
                                else
                                    $class = '';

                                $result .= '<a href="'.$page.'"'.$class.' onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                            }

                            $result .= '<span class="cur">'.($current+1).'</span>';

                            for($page = $current+1,$value = ($current+2); $page < ($current+3); $page++,$value++)
                            {
                                if($page > ($current+1))
                                    $class = ' class="hd"';
                                else
                                    $class = '';

                                $result .= '<a href="'.$page.'"'.$class.' onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                            }

                            $result .= '<span>...</span>';

                            for($page = $c-2, $value = $d; $page < $c; $page++,$value++)
                            {
                                if($page < ($c - 1))
                                    $class = ' class="hd"';
                                else
                                    $class = '';

                                $result .= '<a href="'.$page.'"'.$class.' onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                            }
                        }
                } else {
                    for($page = 0,$value = 1; $page < $c; $page++,$value++)
                    {
                        if($page == $current)
                            $result .= '<span class="cur">'.$value.'</span>';
                        else
                            $result .= '<a href="'.$page.'" onclick="return '.$fce.'('.$page.','.$loading.')">'.$value.'</a>';
                    }
                }

                if($icons)
                {
                    if($current > 0)
                        $result = '<a href="0" class="next" onclick="return '.$fce.'(0,'.$loading.')"><img src="/pic/nav-first-14.png" height="14px" alt="" /></a><a href="'.($current-1).'" class="next" onclick="return '.$fce.'('.($current-1).','.$loading.')"><img src="/pic/nav-back-14.png" height="14px" /></a>'.$result;
                    else
                        $result = '<span class="next"><img src="/pic/nav-first-14.png" height="14px" alt="" /></span><span class="next"><img src="/pic/nav-back-14.png" height="14px" alt="" /></span>'.$result;

                    if($current < $d)
                        $result .= '<a href="'.($current+1).'" class="next" onclick="return '.$fce.'('.($current+1).','.$loading.')"><img src="/pic/nav-next-14.png" height="14px" alt="" /></a><a href="'.$d.'" class="next" onclick="return '.$fce.'('.$d.','.$loading.')"><img src="/pic/nav-last-14.png" height="14px" /></a>';
                    else
                        $result .= '<span class="next"><img src="/pic/nav-next-14.png" height="14px" alt="" /></span><span class="next"><img src="/pic/nav-last-14.png" height="14px" alt="" /></span>';
                }
            }

        return $result;
    }

    public static function getPages($count, $current = '', $page_limit = 50, $loading = 1, $icons = false, $fce = 'goPage') :string
    {
        $result = self::innerPages($count, $current, $page_limit, $loading, $icons, $fce);
        return '<div class="pages">'.$result.'</div>'.self::getLimits($page_limit);
    }

    public static function getPagesScroll($text = 'Scroll') :string
    {
        $result = '<a href="#" id="btnScroll">'.$text.'</a>';
        return '<div class="pages"> '.$result.' </div>';
    }

    public static function getPagesScrollTo($text = 'Nahoru') :string
    {
        $result = '<a href="#" id="btnScrollTo" onclick="windows.scrollTo(0,0);">'.$text.'</a>';
        return '<div class="pages pages-bottom"> '.$result.' </div>';
    }

    public static function getOrders($href, &$order_column, &$order_direction, $accept = []) :array
    {
        $parts = explode('?', $href);
        if(count($parts) === 2)
        {
            if(in_array($parts[0], $accept))
            {
                if($parts[1] === 'desc')
                    $order_direction = 'desc';
                else
                    $order_direction = 'asc';

                $order_column = $parts[0];
            }
        }

        return [$order_column => $order_direction];
    }

    public static function getLimits($selected = 50, $limit_options = null, $class = 'w60 hm')
    {
        if($limit_options === null)
            $limit_options = [
                10 => 10,
                15 => 15,
                20 => 20,
                25 => 25,
                30 => 30,
                40 => 40,
                50 => 50,
                60 => 60,
                70 => 70,
                75 => 75,
                80 => 80,
                90 => 90,
                100 => 100,
                150 => 150,
                200 => 200
            ];

        $box = new TComboBox('limit', $limit_options);
        $box->setClass('limit');
        $box->addClass($class);
        $box->setSelected($selected);

        return '<div class="limits hm">'.$box->html().'</div>';
    }

    public function html() :string
    {
        if($this->childNodes->count() > 0)
        {
            if($this->childNodes->items[0]->nodeName === 'thead')
            {
                $thead = $this->childNodes->items[0];
                if($thead->childNodes->count() > 0)
                {
                    $i = 0;
                    $row = $thead->childNodes->items[0];
                    foreach($row->childNodes->items as $column)
                    {
                        $w = $column->getWidth();
                        if(empty($w))
                            if(count($this->widths) > $i && !empty($this->widths[$i]))
                                $column->setWidth($this->widths[$i]);

                        $i++;
                    }
                }
            } else {
                $i = 0;
                $row = $this->childNodes->items[0];
                foreach($row->childNodes->items as $column)
                {
                    $w = $column->getWidth();
                    if(empty($w))
                        if(count($this->widths) > $i && !empty($this->widths[$i]))
                            $column->setWidth($this->widths[$i]);

                        $i++;
                }
            }
        }

        return parent::html();
    }

}

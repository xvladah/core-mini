<?php

/**
 * Třídy pro práci s elementy a atributy
 *
 * @name TElement
 * @version 1.0
 * @author vladimir.horky
 * @copyright Vladimír Horký, 2018
 */

declare(strict_types=1);

class TElement
{
	public ?string $nodeName;
	public ?string $nodeValue;
	public TAttributeList $attributes;
    public TNodeList $childNodes;

	public function __construct(?string $nodeName, array $attrs = [], mixed $nodeValue = '')
	{
		$this->nodeName = $nodeName;
       	$this->nodeValue = $nodeValue;
        $this->childNodes = new TNodeList();
        $this->attributes = new TAttributeList($attrs);
	}

	public function addHtml(?string $nodeValue) :TElement
	{
		$this->nodeValue .= $nodeValue;
		return $this;
	}

	public function addHtmlBefore(?string $nodeValue) :TElement
	{
		$this->nodeValue = $nodeValue . $this->nodeValue;
		return $this;
	}

	public function setClass(?string $class) :TElement
	{
		$this->attributes->add('class', $class);
		return $this;
	}

	public function addClass(?string $class) :TElement
	{
		if($this->attributes->items['class'] != '')
			$this->attributes->items['class'] = $this->attributes->items['class'].' '.$class;
		else
			$this->setClass($class);

		return $this;
	}

    public function hasClass(string $class) :bool
	{
		if($this->attributes->items['class'] != '')
			return in_array($class, explode(' ', $this->attributes->items['class']));
		else
			return false;
	}

	public function setStyle(?string $style) :TElement
	{
		$this->attributes->add('style', $style);
		return $this;
	}

	public function getStyle() :?string
	{
		return $this->attributes->items['style'];
	}

	public function setId(int|string $id) :TElement
	{
		$this->attributes->add('id', $id);
		return $this;
	}

	public function getId() :null|int|string
	{
		return $this->attributes->items['id'];
	}

	public function add($node, ?string $name = null) :TElement
	{
		$this->childNodes->add($node, $name);
		return $this;
	}

	public function addBefore($node, ?string $name = null) :TElement
	{
		$this->childNodes->addBefore($node, $name);
		return $this;
	}

	public function html() :string
	{
       	$result = $this->nodeValue . $this->childNodes->html();
      	return '<'.$this->nodeName.$this->attributes->html().'>'.$result.'</'.$this->nodeName.'>';
	}

	public function innerHtml() :string
	{
		return $this->nodeValue . $this->childNodes->html();
	}
}
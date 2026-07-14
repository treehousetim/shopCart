<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\catalogTotalType;
use treehousetim\shopCart\catalogTotalTypeLoaderInterface;

// drives cart::populateTotalTypes(): resetType() rewinds, getType() returns the
// current type, nextType() advances and reports whether another remains
class fixtureTotalTypeLoader implements catalogTotalTypeLoaderInterface
{
	protected $types;
	protected $index = 0;

	public function __construct( array $types )
	{
		$this->types = array_values( $types );
	}
	//------------------------------------------------------------------------
	public function resetType()
	{
		$this->index = 0;
	}
	//------------------------------------------------------------------------
	public function nextType() : bool
	{
		$this->index++;
		return $this->index < count( $this->types );
	}
	//------------------------------------------------------------------------
	public function getType() : catalogTotalType
	{
		return $this->types[$this->index];
	}
}

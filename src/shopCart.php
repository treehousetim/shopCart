<?php namespace treehousetim\shopCart;

class cartMetalCollection
{
	public $values = [];
	public function addMetal( string $metal )
	{
		if( ! in_array( $metal, $this->values ) )
		{
				$this->values[] = $metal;
		}
	}
}

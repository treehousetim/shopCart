<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\cartData;

// concrete cartData: jsonSerialize simply returns the stored data
class fixtureCartData extends cartData
{
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->data;
	}
}

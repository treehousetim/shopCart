<?php namespace treehousetim\shopCart;

interface catalogTotalTypeLoaderInterface
{
	public function nextType() : bool;
	public function getType() : catalogTotalType;
	public function resetType();
}

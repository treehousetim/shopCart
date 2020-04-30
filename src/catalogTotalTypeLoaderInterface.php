<?php namespace treehousetim\shopCart;

interface catalogTotalTypeLoaderInterface
{
	public function nextType() : bool;
	public function getType() : catalogTotalType;
	public function resetType();
}



// // gold, silver, platinum, palladium (weight)
// // price

// class ownxWeightTotalType extends catalogTotalType
// {

// }
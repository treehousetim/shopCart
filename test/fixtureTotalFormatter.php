<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\catalogTotalType;
use treehousetim\shopCart\totalFormatterInterface;

// a trivial, inspectable total formatter: returns "<identifier>:<value>" so
// tests can assert both that the value flowed through and which type was used
class fixtureTotalFormatter implements totalFormatterInterface
{
	public function formatTotalType( string $value, catalogTotalType $type )
	{
		return $type->getIdentifier() . ':' . $value;
	}
}

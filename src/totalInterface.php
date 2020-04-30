<?php namespace treehousetim\shopCart;

interface totalInterface
{
	public function getTotal( catalogTotalType $type ) : string; // return type is a string from bc math function calls
	public function getTotalFormatted( catalogTotalType $type ) : string;
	public function formatTotalType( string $value, catalogTotalType $type );
}


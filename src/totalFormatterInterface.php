<?php namespace treehousetim\shopCart;

interface totalFormatterInterface
{
	public function formatTotalType( string $value, catalogTotalType $type );
}
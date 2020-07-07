<?php namespace treehousetim\shopCart;

interface iCartData
{
	public function jsonSerialize();
	public function getForStorage();
	public function setData( $data ) : iCartData;
	public function getData();
	public function setType( string $type ) : iCartData;
	public function getType();
}
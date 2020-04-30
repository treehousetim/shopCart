<?php namespace treehousetim\shopCart;

interface catalogLoaderInterface
{
	public function hasProducts() : bool;
	public function nextProduct() : bool;
	public function getProduct() : product;
}
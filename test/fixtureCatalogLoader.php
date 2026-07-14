<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\catalogLoaderInterface;
use treehousetim\shopCart\product;

// drives catalog::populate() over an injected list of products.
// mirrors the cursor-style contract: hasProducts() gates entry, getProduct()
// returns the current product, nextProduct() advances and reports if more remain
class fixtureCatalogLoader implements catalogLoaderInterface
{
	protected $products;
	protected $index = 0;

	public function __construct( array $products )
	{
		$this->products = array_values( $products );
	}
	//------------------------------------------------------------------------
	public function hasProducts() : bool
	{
		return count( $this->products ) > 0;
	}
	//------------------------------------------------------------------------
	public function nextProduct() : bool
	{
		$this->index++;
		return $this->index < count( $this->products );
	}
	//------------------------------------------------------------------------
	public function getProduct() : product
	{
		return $this->products[$this->index];
	}
}

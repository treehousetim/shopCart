<?php namespace treehousetim\shopCart;

class catalog
{
	protected $products = [];
	protected $productLoader;

	public function setProductLoader( catalogLoaderInterface $loader ) : self
	{
		$this->productLoader = $loader;
		return $this;
	}
	//------------------------------------------------------------------------
	public function addProduct( product $product ) : self
	{
		$this->products[$product->getId()] = $product;

		return $this;
	}
	//------------------------------------------------------------------------
	public function getProducts() : array
	{
		return $this->products;
	}
	//------------------------------------------------------------------------
	public function populate() : self
	{
		if( $this->productLoader->hasProducts()  )
		{
			do
			{
				$product = $this->productLoader->getProduct();
				$this->addProduct( $product );
			} while( $this->productLoader->nextProduct() );
		}

		return $this;
	}
	//------------------------------------------------------------------------
	public function getProductById( $id ) : product
	{
		if( $this->hasProductId( $id ) )
		{
			return $this->products[$id];
		}

		throw new Exception( 'No Product', Exception::notItemErrorCode );
	}
	//------------------------------------------------------------------------
	public function hasProductId( $id ) : bool
	{
		return array_key_exists( $id, $this->products );
	}
}

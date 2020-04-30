<?php namespace treehousetim\shopCart;

class cartItem
{
	protected $qty;
	protected $product;

	public function __construct( product $product )
	{
		$this->product = $product;
		$this->qty = 0;
	}
	//------------------------------------------------------------------------
	public function updateQty( $qty )
	{
		$this->qty = $qty;
	}
	//------------------------------------------------------------------------
	public function addQty( $qty )
	{
		$this->qty += $qty;
	}
	//------------------------------------------------------------------------
	public function setQty( int $qty ) : self
	{
		$this->qty = $qty;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getQty() : int
	{
		return $this->qty;
	}
	//------------------------------------------------------------------------
	public function getProduct() : product
	{
		return $this->product;
	}
	// //------------------------------------------------------------------------
	// public function getTotalWeight()
	// {
	// 	return bcmul( $this->qty, $this->product->weight );
	// }
	// //------------------------------------------------------------------------
	// public function getTotalPremium()
	// {
	// 	return bcmul( $this->qty, $this->product->premium );
	// }
	// //------------------------------------------------------------------------
	// public function formatWeight()
	// {
	// 	return unitFormatAutoScale( $this->getTotalWeight(), $this->product->unit );
	// }
	// //------------------------------------------------------------------------
	// public function formatPremium()
	// {
	// 	return moneyFormat( $this->getTotalPremium() );
	// }
	// //------------------------------------------------------------------------
	// public function getTotalCategories() : array
	// {
	// 	return $this->product->getTotalCategories();
	// }
	// //------------------------------------------------------------------------
	// public function getTotalFormatted( string $type )
	// {
	// 	return $this->formatTotalType( $this->getTotal( $type ), $type, [] );
	// }
	// //------------------------------------------------------------------------
	// public function getTotalTypes() : array
	// {
	// 	return $this->product->getTotalTypes();
	// }
	//------------------------------------------------------------------------
	public function formatAmount( string $type )
	{
		return $this->product->getFormatter()->formatCartItem( $type, $this );
	}
	//------------------------------------------------------------------------
	public function getTotalAmount( catalogTotalType $type ) : string
	{
		return bcmul( $this->qty, $this->product->getAmountForCatalogTotalType( $type ) );
	}
	//------------------------------------------------------------------------
	public function getTotalTypeAmount( catalogTotalType $type ) : string
	{
		if( $this->product->getTotalTypeIdentifier() == $type->getIdentifier() )
		{
			return bcmul( $this->qty, $this->product->getAmountForCatalogTotalType( $type ) );
		}

		return '0';
	}
	//------------------------------------------------------------------------
	public function formatTotalType( string $value, string $type, array $params )
	{
		return $this->product->formatTotalType( $value, $type, $params );
	}
}
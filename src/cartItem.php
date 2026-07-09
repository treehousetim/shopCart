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
	protected function requireValidQty( $qty )
	{
		if( $this->product->isSerialized() && $qty != 1 )
		{
			throw new Exception( 'Serialized items must have a quantity of exactly 1', Exception::serializedQtyErrorCode );
		}
	}
	//------------------------------------------------------------------------
	public function updateQty( $qty )
	{
		$this->requireValidQty( $qty );
		$this->qty = $qty;
	}
	//------------------------------------------------------------------------
	public function addQty( $qty )
	{
		$this->requireValidQty( $this->qty + $qty );
		$this->qty += $qty;
	}
	//------------------------------------------------------------------------
	public function setQty( int $qty ) : self
	{
		$this->requireValidQty( $qty );
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
	//------------------------------------------------------------------------
	public function formatAmount( string $type )
	{
		return $this->product->getFormatter()->formatCartItem( $type, $this );
	}
	//------------------------------------------------------------------------
	public function getTotalAmount( catalogTotalType $type ) : string
	{
		return bcmul( $this->qty, $this->product->getAmountForCatalogTotalType( $type ), formatting::$longScale );
	}
	//------------------------------------------------------------------------
	public function getTotalTypeAmount( catalogTotalType $type ) : string
	{
		if( $this->product->getTotalTypeIdentifier() == $type->getIdentifier() )
		{
			return bcmul( $this->qty, $this->product->getAmountForCatalogTotalType( $type ), formatting::$longScale );
		}

		return '0';
	}
}
<?php namespace treehousetim\shopCart;

class productAmountFormatterPrice extends productAmountFormatter
{
	public function formatProduct( string $type, product $product ) : string
	{
		$this->requirePriceType( $type );
		return $this->formatProductPrice( $product );
	}
	//------------------------------------------------------------------------
	public function formatCartItem( string $type, cartItem $cartItem ) : string
	{
		$this->requirePriceType( $type );
		return $this->formatCartItemPrice( $cartItem );
	}
	//------------------------------------------------------------------------
	public function formatCartTotal( catalogTotalType $type, string $total ) : string
	{
		$this->requirePriceType( $type->getType() );
		return $this->formatCartTotalPrice( $total );
	}
	//------------------------------------------------------------------------
	protected function requirePriceType( string $type )
	{
		if( $type != self::tPRICE )
		{
			throw new Exception( 'Unable to format ' . $type . ' using built in productAmountFormatterPrice class', Exception::invalidFormatErrorCode );
		}
	}
}

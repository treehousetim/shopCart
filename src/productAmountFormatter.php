<?php namespace treehousetim\shopCart;

abstract class productAmountFormatter
{
	const tPRICE = 'price';

	abstract public function formatProduct( string $type, product $product ) : string;
	abstract public function formatCartItem( string $type, cartItem $cartItem ) : string;
	abstract public function formatCartTotal( catalogTotalType $type, string $total ) : string;
	//------------------------------------------------------------------------
	public function formatProductPrice( product $product )
	{
		return formatting::moneyFormat( $product->getPrice() );
	}
	//------------------------------------------------------------------------
	public function formatCartItemPrice( cartItem $cartItem ) : string
	{
		return formatting::moneyFormat( bcmul( $cartItem->getQty(), $cartItem->getProduct()->getPrice() ) );
	}
	//------------------------------------------------------------------------
	public function formatCartTotalPrice( string $total ): string
	{
		return formatting::moneyFormat( $total );
	}
}
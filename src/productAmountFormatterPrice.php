<?php namespace treehousetim\shopCart;

class productAmountFormatterPrice extends productAmountFormatter
{
	public function format( product $product ) : string
	{
		if( $this->type == productAmountFormatter::tPrice )
		{
			return formatting::moneyFormat( $product->getPrice() );
		}

		throw new \Exception( 'Unable to format ' . $this->type . ' using built in productAmountFormatterPrice class' );
	}
}
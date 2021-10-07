<?php namespace treehousetim\shopCart;

class cartPriceTotalFormatter implements totalFormatterInterface
{
	public function formatTotalType( string $value, catalogTotalType $type )
	{
		switch( $type->type )
		{
		case catalogTotalType::tPRODUCT_PRICE:
			return formatting::moneyFormat( $value );
			break;

		default:
			throw new Exception( 'Only price is supported in cartPriceTotalFormatter', Exception::invalidFormatErrorCode );
		}
	}
}
<?php namespace treehousetim\shopCart\test;

use PHPUnit\Framework\TestCase;
use treehousetim\shopCart\catalogTotalType;

abstract class testBase extends TestCase
{
	protected function priceType() : catalogTotalType
	{
		return ( new catalogTotalType( catalogTotalType::tPRODUCT_PRICE ) )
			->setIdentifier( 'price' )
			->setLabel( 'Order Total' );
	}
	//------------------------------------------------------------------------
	protected function pointsType() : catalogTotalType
	{
		return ( new catalogTotalType( catalogTotalType::tPRODUCT_FIELD ) )
			->setIdentifier( 'points' )
			->setProductField( 'points' )
			->setLabel( 'Points' );
	}
	//------------------------------------------------------------------------
	protected function makeProduct( string $id, string $price, string $points = '0' ) : fixtureProduct
	{
		return ( new fixtureProduct() )
			->setPoints( $points )
			->setId( $id )
			->setName( 'Product ' . $id )
			->setShortDesc( 'Description of ' . $id )
			->setPrice( $price )
			->setTotalTypeIdentifier( 'price' );
	}
}

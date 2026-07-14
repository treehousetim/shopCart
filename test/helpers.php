<?php

use treehousetim\shopCart\catalogTotalType;
use treehousetim\shopCart\test\fixtureProduct;

/*
| Shared test helpers (replaces the old testBase class). Loaded via composer
| autoload-dev "files" so they are available to every Pest test.
*/

if( ! function_exists( 'priceType' ) )
{
	//------------------------------------------------------------------------
	// a price total type identified by 'price'
	function priceType() : catalogTotalType
	{
		return ( new catalogTotalType( catalogTotalType::tPRODUCT_PRICE ) )
			->setIdentifier( 'price' )
			->setLabel( 'Order Total' );
	}
	//------------------------------------------------------------------------
	// a second, product-field dimension identified by 'points'
	function pointsType() : catalogTotalType
	{
		return ( new catalogTotalType( catalogTotalType::tPRODUCT_FIELD ) )
			->setIdentifier( 'points' )
			->setProductField( 'points' )
			->setLabel( 'Points' );
	}
	//------------------------------------------------------------------------
	function makeProduct( string $id, string $price, string $points = '0' ) : fixtureProduct
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

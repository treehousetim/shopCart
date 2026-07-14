<?php

use treehousetim\shopCart\cartItem;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\productAmountFormatterPrice;
use treehousetim\shopCart\productVariationSerialized;

it( 'starts at zero quantity', function()
{
	$item = new cartItem( makeProduct( 'a', '1.00' ) );

	expect( $item->getQty() )->toBe( 0 );
} );

it( 'sets, adds, and updates quantity', function()
{
	$item = new cartItem( makeProduct( 'a', '1.00' ) );

	$item->setQty( 5 );
	expect( $item->getQty() )->toBe( 5 );

	$item->addQty( 2 );
	expect( $item->getQty() )->toBe( 7 );

	$item->updateQty( 3 );
	expect( $item->getQty() )->toBe( 3 );
} );

it( 'exposes its product', function()
{
	$product = makeProduct( 'a', '1.00' );
	$item = new cartItem( $product );

	expect( $item->getProduct() )->toBe( $product );
} );

it( 'rejects a non-unit quantity for a serialized product', function()
{
	$parent = makeProduct( 'widget', '10.00' );
	$serial = ( new productVariationSerialized( $parent ) )->setSerialNumber( 'SN-1' );
	$item = new cartItem( $serial );

	// exactly one is allowed
	$item->setQty( 1 );
	expect( $item->getQty() )->toBe( 1 );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::serializedQtyErrorCode );

	$item->setQty( 2 );
} );

it( 'computes a total amount at full bcmath scale', function()
{
	$item = new cartItem( makeProduct( 'a', '19.99' ) );
	$item->setQty( 3 );

	// 3 x 19.99 keeps six decimals, not truncated to 59
	expect( $item->getTotalAmount( priceType() ) )->toBe( '59.970000' );
} );

it( 'returns the type amount only when the identifier matches', function()
{
	$item = new cartItem( makeProduct( 'a', '19.99' ) );
	$item->setQty( 3 );

	// product's totalTypeIdentifier is 'price'
	expect( $item->getTotalTypeAmount( priceType() ) )->toBe( '59.970000' );

	// points identifier does not match, so it contributes nothing
	expect( $item->getTotalTypeAmount( pointsType() ) )->toBe( '0' );
} );

it( 'delegates formatAmount to the product formatter', function()
{
	$product = makeProduct( 'a', '19.99' );
	$product->setFormatter( new productAmountFormatterPrice() );

	$item = new cartItem( $product );
	$item->setQty( 3 );

	expect( $item->formatAmount( productAmountFormatterPrice::tPRICE ) )
		->toBe( '$ <span class="number">59.97</span>' );
} );

<?php

use treehousetim\shopCart\cart;
use treehousetim\shopCart\catalog;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\productVariationSerialized;

// a serialized unit of a 'widget' parent ($10.00 / 2 points)
function makeSerializedUnit( string $serial, $price = null, $points = null ) : productVariationSerialized
{
	$parent = makeProduct( 'widget', '10.00', '2' );

	$unit = ( new productVariationSerialized( $parent ) )
		->setSerialNumber( $serial );

	if( $price !== null )
	{
		$unit->setPrice( $price );
	}

	if( $points !== null )
	{
		$unit->setFieldAmount( 'points', $points );
	}

	return $unit;
}

it( 'derives its id from parent id and serial number', function()
{
	$unit = makeSerializedUnit( 'SN-0001' );

	expect( $unit->getId() )->toBe( 'widget:SN-0001' );
	expect( $unit->getSerialNumber() )->toBe( 'SN-0001' );
	expect( $unit->isSerialized() )->toBeTrue();
} );

it( 'lets an explicit id win over the derived one', function()
{
	$unit = makeSerializedUnit( 'SN-0001' )->setId( 'my-own-id' );

	expect( $unit->getId() )->toBe( 'my-own-id' );
} );

it( 'throws when the same serial is added twice', function()
{
	$unit = makeSerializedUnit( 'SN-0001' );
	$cart = new cart( ( new catalog() )->addProduct( $unit ) );

	$cart->addProduct( $unit, 1 );
	expect( $cart->getItemByProductId( $unit->getId() )->getQty() )->toBe( 1 );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::duplicateSerialErrorCode );

	$cart->addProduct( $unit, 1 );
} );

it( 'leaves quantity unchanged after a rejected duplicate add', function()
{
	$unit = makeSerializedUnit( 'SN-0001' );
	$cart = new cart( ( new catalog() )->addProduct( $unit ) );

	$cart->addProduct( $unit, 1 );

	try
	{
		$cart->addProduct( $unit, 1 );
		$this->fail( 'expected duplicate serial Exception' );
	}
	catch( Exception $e )
	{
		expect( $e->getCode() )->toBe( Exception::duplicateSerialErrorCode );
	}

	expect( $cart->getItemByProductId( $unit->getId() )->getQty() )->toBe( 1 );
	expect( $cart->getTotalQty() )->toBe( '1' );
} );

it( 'throws when added with a quantity other than one', function()
{
	$unit = makeSerializedUnit( 'SN-0001' );
	$cart = new cart( ( new catalog() )->addProduct( $unit ) );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::serializedQtyErrorCode );

	$cart->addProduct( $unit, 2 );
} );

it( 'guards updateItemQty for serialized items', function()
{
	$unit = makeSerializedUnit( 'SN-0001' );
	$cart = new cart( ( new catalog() )->addProduct( $unit ) );

	$cart->addProduct( $unit, 1 );

	// updating to 1 is a no-op and allowed
	$cart->updateItemQty( $unit->getId(), 1 );
	expect( $cart->getItemByProductId( $unit->getId() )->getQty() )->toBe( 1 );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::serializedQtyErrorCode );

	$cart->updateItemQty( $unit->getId(), 3 );
} );

it( 'carries per-serial amounts in every total type', function()
{
	$parent = makeProduct( 'widget', '10.00', '2' );

	$unitA = ( new productVariationSerialized( $parent ) )
		->setSerialNumber( 'SN-A' )
		->setPrice( '12.345678' )
		->setFieldAmount( 'points', '7.5' );

	$unitB = ( new productVariationSerialized( $parent ) )
		->setSerialNumber( 'SN-B' )
		->setPrice( '11.111111' )
		->setFieldAmount( 'points', '3.25' );

	expect( $unitA->getAmountForCatalogTotalType( priceType() ) )->toBe( '12.345678' );
	expect( $unitA->getAmountForCatalogTotalType( pointsType() ) )->toBe( '7.5' );
	expect( $unitB->getAmountForCatalogTotalType( priceType() ) )->toBe( '11.111111' );
	expect( $unitB->getAmountForCatalogTotalType( pointsType() ) )->toBe( '3.25' );
} );

it( 'feeds per-serial amounts into cart totals', function()
{
	$parent = makeProduct( 'widget', '10.00', '2' );

	$unitA = ( new productVariationSerialized( $parent ) )
		->setSerialNumber( 'SN-A' )
		->setPrice( '12.345678' )
		->setFieldAmount( 'points', '7.5' );

	$unitB = ( new productVariationSerialized( $parent ) )
		->setSerialNumber( 'SN-B' )
		->setPrice( '11.111111' )
		->setFieldAmount( 'points', '3.25' );

	$catalog = ( new catalog() )
		->addProduct( $parent )
		->addProduct( $unitA )
		->addProduct( $unitB );

	$cart = new cart( $catalog );
	$cart->addProduct( $parent, 3 );
	$cart->addProduct( $unitA, 1 );
	$cart->addProduct( $unitB, 1 );

	// 3 x 10.00 + 12.345678 + 11.111111 = 53.456789
	expect( $cart->getAmountTotal( priceType() ) )->toBe( '53.456789' );

	// getTotal() only counts items whose totalTypeIdentifier matches;
	// serialized units fall back to the parent's identifier ('price')
	expect( $cart->getTotal( priceType() ) )->toBe( '53.456789' );

	// 3 x 2 + 7.5 + 3.25 = 16.75
	expect( $cart->getAmountTotal( pointsType() ) )->toBe( '16.750000' );
} );

it( 'uses parent amounts when a serial has no overrides', function()
{
	$unit = makeSerializedUnit( 'SN-PLAIN' );

	expect( $unit->getAmountForCatalogTotalType( priceType() ) )->toBe( '10.00' );
	expect( $unit->getAmountForCatalogTotalType( pointsType() ) )->toBe( '2' );
} );

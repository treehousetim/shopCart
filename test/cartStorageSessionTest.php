<?php

use treehousetim\shopCart\cart;
use treehousetim\shopCart\catalog;
use treehousetim\shopCart\cartStorageSession;
use treehousetim\shopCart\productVariation;
use treehousetim\shopCart\productVariationSerialized;
use treehousetim\shopCart\test\fixtureCartData;

// start a session once and reset its contents before each test so the storage
// handler always sees a clean, active session
beforeEach( function()
{
	if( session_status() != PHP_SESSION_ACTIVE )
	{
		session_start();
	}

	$_SESSION = [];
} );

// a catalog with a plain product, a priced variation, and two serialized units
function buildStorageCatalog() : catalog
{
	$parent = makeProduct( 'shirt', '20.00', '4' );

	$variation = ( new productVariation( $parent ) )
		->setAttribute( 'size', 'L' )
		->setId( 'shirt-l' )
		->setPrice( '22.00' );

	$unitA = ( new productVariationSerialized( $parent ) )
		->setSerialNumber( 'SN-A' )
		->setPrice( '25.123456' )
		->setFieldAmount( 'points', '5.5' );

	$unitB = ( new productVariationSerialized( $parent ) )
		->setSerialNumber( 'SN-B' )
		->setPrice( '26.654321' )
		->setFieldAmount( 'points', '6.25' );

	return ( new catalog() )
		->addProduct( $parent )
		->addProduct( $variation )
		->addProduct( $unitA )
		->addProduct( $unitB );
}

it( 'round trips a cart through the session', function()
{
	$catalog = buildStorageCatalog();

	$cartOut = ( new cart( $catalog ) )
		->setStorageHandler( new cartStorageSession() );

	$cartOut->addProduct( $catalog->getProductById( 'shirt' ), 2 );
	$cartOut->addProduct( $catalog->getProductById( 'shirt-l' ), 3 );
	$cartOut->addProduct( $catalog->getProductById( 'shirt:SN-A' ), 1 );
	$cartOut->addProduct( $catalog->getProductById( 'shirt:SN-B' ), 1 );
	$cartOut->save();

	// a fresh cart over the same catalog, loaded from the session,
	// must resolve every variation and serialized unit by its id
	$cartIn = ( new cart( $catalog ) )
		->setStorageHandler( new cartStorageSession() )
		->load();

	expect( $cartIn->getDistinctItemQty() )->toBe( '4' );
	expect( $cartIn->getTotalQty() )->toBe( '7' );

	expect( $cartIn->getItemByProductId( 'shirt' )->getQty() )->toBe( 2 );
	expect( $cartIn->getItemByProductId( 'shirt-l' )->getQty() )->toBe( 3 );
	expect( $cartIn->getItemByProductId( 'shirt:SN-A' )->getQty() )->toBe( 1 );
	expect( $cartIn->getItemByProductId( 'shirt:SN-B' )->getQty() )->toBe( 1 );

	// the loaded items reference the same catalog product instances
	expect( $cartIn->getItemByProductId( 'shirt:SN-A' )->getProduct() )
		->toBe( $catalog->getProductById( 'shirt:SN-A' ) );
	expect( $cartIn->getItemByProductId( 'shirt-l' )->getProduct() )
		->toBe( $catalog->getProductById( 'shirt-l' ) );

	// totals survive the round trip in both dimensions
	// price: 2 x 20.00 + 3 x 22.00 + 25.123456 + 26.654321 = 157.777777
	expect( $cartIn->getAmountTotal( priceType() ) )->toBe( '157.777777' );
	expect( $cartIn->getAmountTotal( priceType() ) )
		->toBe( $cartOut->getAmountTotal( priceType() ) );

	// points: 2 x 4 + 3 x 4 + 5.5 + 6.25 = 31.75
	expect( $cartIn->getAmountTotal( pointsType() ) )->toBe( '31.750000' );
	expect( $cartIn->getAmountTotal( pointsType() ) )
		->toBe( $cartOut->getAmountTotal( pointsType() ) );
} );

it( 'stores only id and qty for each item', function()
{
	$catalog = buildStorageCatalog();

	$cart = ( new cart( $catalog ) )
		->setStorageHandler( new cartStorageSession() );

	$cart->addProduct( $catalog->getProductById( 'shirt:SN-A' ), 1 );
	$cart->save();

	expect( $_SESSION['cart_items'] )->toBe( [ [ 'id' => 'shirt:SN-A', 'qty' => 1 ] ] );
} );

it( 'round trips cart data through the session', function()
{
	$catalog = buildStorageCatalog();

	$cartOut = ( new cart( $catalog ) )
		->setStorageHandler( new cartStorageSession() );

	$cartOut->addProduct( $catalog->getProductById( 'shirt' ), 1 );
	$cartOut->addData(
		( new fixtureCartData() )
			->setType( 'coupon' )
			->setData( [ 'code' => 'SAVE10', 'pct' => 10 ] )
	);
	$cartOut->save();

	// simulate the session being written out and read back on a later request:
	// the stored iCartData objects must survive php's session serialization
	$_SESSION = unserialize( serialize( $_SESSION ) );

	$cartIn = ( new cart( $catalog ) )
		->setStorageHandler( new cartStorageSession() )
		->load();

	expect( $cartIn->hasCartDataType( 'coupon' ) )->toBeTrue();

	$data = $cartIn->getDataByType( 'coupon' );
	expect( $data->getType() )->toBe( 'coupon' );
	expect( $data->getData() )->toBe( [ 'code' => 'SAVE10', 'pct' => 10 ] );
} );

it( 'empties both session buckets on emptyCart', function()
{
	$catalog = buildStorageCatalog();
	$storage = new cartStorageSession();

	$cart = ( new cart( $catalog ) )->setStorageHandler( $storage );
	$cart->addProduct( $catalog->getProductById( 'shirt' ), 1 );
	$cart->save();

	expect( $_SESSION['cart_items'] )->not->toBe( [] );

	$storage->emptyCart( $cart );

	expect( $_SESSION['cart_items'] )->toBe( [] );
	expect( $_SESSION['cart_data'] )->toBe( [] );
} );

it( 'skips stored items whose id is no longer in the catalog', function()
{
	// a stale id (e.g. a product removed since the cart was saved) is dropped
	$_SESSION['cart_items'] = [
		[ 'id' => 'gone', 'qty' => 2 ],
		[ 'id' => 'shirt', 'qty' => 1 ],
	];

	$catalog = buildStorageCatalog();

	$cart = ( new cart( $catalog ) )
		->setStorageHandler( new cartStorageSession() )
		->load();

	expect( $cart->hasItemForProductId( 'shirt' ) )->toBeTrue();
	expect( $cart->hasItemForProductId( 'gone' ) )->toBeFalse();
	expect( $cart->getDistinctItemQty() )->toBe( '1' );
} );

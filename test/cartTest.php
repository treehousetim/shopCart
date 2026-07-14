<?php

use treehousetim\shopCart\cart;
use treehousetim\shopCart\catalog;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\productAmountFormatterPrice;
use treehousetim\shopCart\test\fixtureCartData;
use treehousetim\shopCart\test\fixtureStorage;
use treehousetim\shopCart\test\fixtureTotalTypeLoader;

// a cart over a catalog holding products a ($10, identifier 'price') and
// b ($5, identifier 'points')
function twoDimensionCart() : cart
{
	$a = makeProduct( 'a', '10.00' );
	$b = makeProduct( 'b', '5.00' )->setTotalTypeIdentifier( 'points' );

	$catalog = ( new catalog() )->addProduct( $a )->addProduct( $b );

	return new cart( $catalog );
}

it( 'exposes its catalog', function()
{
	$catalog = new catalog();
	expect( ( new cart( $catalog ) )->getCatalog() )->toBe( $catalog );
} );

it( 'adds a new product and merges quantity on re-add', function()
{
	$catalog = ( new catalog() )->addProduct( makeProduct( 'a', '10.00' ) );
	$cart = new cart( $catalog );
	$product = $catalog->getProductById( 'a' );

	$cart->addProduct( $product, 2 );
	$cart->addProduct( $product, 3 );

	expect( $cart->getDistinctItemQty() )->toBe( '1' );
	expect( $cart->getTotalQty() )->toBe( '5' );
	expect( $cart->getItemByProductId( 'a' )->getQty() )->toBe( 5 );
} );

it( 'distinguishes getTotal (identifier-matched) from getAmountTotal (all items)', function()
{
	$cart = twoDimensionCart();
	$cart->addProduct( $cart->getCatalog()->getProductById( 'a' ), 1 );
	$cart->addProduct( $cart->getCatalog()->getProductById( 'b' ), 1 );

	// getAmountTotal sums every item's amount for the type: 10 + 5
	expect( $cart->getAmountTotal( priceType() ) )->toBe( '15.000000' );

	// getTotal only sums items whose totalTypeIdentifier matches 'price';
	// product b carries the 'points' identifier and contributes 0
	expect( $cart->getTotal( priceType() ) )->toBe( '10.000000' );
} );

it( 'formats amount and grand totals through the cart formatter', function()
{
	$cart = twoDimensionCart()->setFormatter( new productAmountFormatterPrice() );
	$cart->addProduct( $cart->getCatalog()->getProductById( 'a' ), 1 );
	$cart->addProduct( $cart->getCatalog()->getProductById( 'b' ), 1 );

	expect( $cart->getAmountTotalFormatted( priceType() ) )
		->toBe( '$ <span class="number">15.00</span>' );

	expect( $cart->getTotalFormatted( priceType() ) )
		->toBe( '$ <span class="number">10.00</span>' );
} );

it( 'removes an item', function()
{
	$catalog = ( new catalog() )->addProduct( makeProduct( 'a', '10.00' ) );
	$cart = new cart( $catalog );
	$cart->addProduct( $catalog->getProductById( 'a' ), 1 );

	$cart->removeItem( $cart->getItemByProductId( 'a' ) );

	expect( $cart->hasItemForProductId( 'a' ) )->toBeFalse();
	expect( $cart->getDistinctItemQty() )->toBe( '0' );
} );

it( 'updates an item quantity', function()
{
	$catalog = ( new catalog() )->addProduct( makeProduct( 'a', '10.00' ) );
	$cart = new cart( $catalog );
	$cart->addProduct( $catalog->getProductById( 'a' ), 1 );

	$cart->updateItemQty( 'a', 5 );

	expect( $cart->getItemByProductId( 'a' )->getQty() )->toBe( 5 );
} );

it( 'throws when requiring or fetching an unknown product id', function()
{
	$cart = new cart( new catalog() );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::notItemErrorCode );

	$cart->getItemByProductId( 'nope' );
} );

it( 'empties items and data through the storage handler', function()
{
	$catalog = ( new catalog() )->addProduct( makeProduct( 'a', '10.00' ) );
	$cart = ( new cart( $catalog ) )->setStorageHandler( new fixtureStorage() );
	$cart->addProduct( $catalog->getProductById( 'a' ), 1 );
	$cart->addData( ( new fixtureCartData() )->setType( 'coupon' )->setData( [ 'x' => 1 ] ) );

	$cart->emptyCart();

	expect( $cart->getDistinctItemQty() )->toBe( '0' );
	expect( $cart->getCartData() )->toBe( [] );
	expect( $cart->getCartItems() )->toBe( [] );
} );

it( 'populates total types from a loader', function()
{
	$cart = ( new cart( new catalog() ) )
		->setTotalTypeLoader( new fixtureTotalTypeLoader( [ priceType(), pointsType() ] ) );

	$types = $cart->getTotalTypes();

	expect( $types )->toHaveCount( 2 );
	expect( $types[0]->getIdentifier() )->toBe( 'price' );
	expect( $types[1]->getIdentifier() )->toBe( 'points' );
} );

it( 'stores, reads, and removes cart data by type', function()
{
	$cart = new cart( new catalog() );
	$data = ( new fixtureCartData() )->setType( 'coupon' )->setData( [ 'code' => 'SAVE10' ] );

	$cart->addData( $data );

	expect( $cart->hasCartDataType( 'coupon' ) )->toBeTrue();
	expect( $cart->hasCartDataType( 'missing' ) )->toBeFalse();
	expect( $cart->getDataByType( 'coupon' ) )->toBe( $data );
	expect( $cart->getCartData() )->toBe( [ 'coupon' => $data ] );

	$cart->removeDataByType( 'coupon' );
	expect( $cart->hasCartDataType( 'coupon' ) )->toBeFalse();
} );

it( 'throws when reading an unknown cart data type', function()
{
	$cart = new cart( new catalog() );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::invalidDataTypeErrorCode );

	$cart->getDataByType( 'missing' );
} );

it( 'throws when removing an unknown cart data type', function()
{
	$cart = new cart( new catalog() );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::invalidDataTypeErrorCode );

	$cart->removeDataByType( 'missing' );
} );

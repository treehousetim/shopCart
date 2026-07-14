<?php

use treehousetim\shopCart\cart;
use treehousetim\shopCart\cartPriceTotalFormatter;
use treehousetim\shopCart\catalog;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\productAmountFormatterPrice;

it( 'formats a product price', function()
{
	$formatter = new productAmountFormatterPrice();
	$product = makeProduct( 'sku-1', '19.99' );

	expect( $formatter->formatProduct( productAmountFormatterPrice::tPRICE, $product ) )
		->toBe( '$ <span class="number">19.99</span>' );
} );

it( 'formats a cart item line at full bcmath scale', function()
{
	// regression: without an explicit scale, bcmul truncated 3 x 19.99 to 59
	$formatter = new productAmountFormatterPrice();
	$catalog = ( new catalog() )->addProduct( makeProduct( 'sku-1', '19.99' ) );
	$cart = new cart( $catalog );
	$cart->addProduct( $catalog->getProductById( 'sku-1' ), 3 );

	$item = $cart->getItemByProductId( 'sku-1' );

	expect( $formatter->formatCartItem( productAmountFormatterPrice::tPRICE, $item ) )
		->toBe( '$ <span class="number">59.97</span>' );
} );

it( 'formats a cart total', function()
{
	$formatter = new productAmountFormatterPrice();

	expect( $formatter->formatCartTotal( priceType(), '157.777777' ) )
		->toBe( '$ <span class="number">157.78</span>' );
} );

it( 'throws when asked to format a product with a non-price type', function()
{
	$formatter = new productAmountFormatterPrice();

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::invalidFormatErrorCode );

	$formatter->formatProduct( 'points', makeProduct( 'sku-1', '19.99' ) );
} );

it( 'throws when asked to format a cart total with a non-price type', function()
{
	$formatter = new productAmountFormatterPrice();

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::invalidFormatErrorCode );

	// pointsType()'s underlying type is tPRODUCT_FIELD, not price
	$formatter->formatCartTotal( pointsType(), '10.00' );
} );

it( 'formats a total type price via cartPriceTotalFormatter', function()
{
	$formatter = new cartPriceTotalFormatter();

	expect( $formatter->formatTotalType( '19.99', priceType() ) )
		->toBe( '$ <span class="number">19.99</span>' );
} );

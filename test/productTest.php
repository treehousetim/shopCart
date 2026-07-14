<?php

use treehousetim\shopCart\catalogTotalType;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\productAmountFormatterPrice;
use treehousetim\shopCart\test\fixtureProduct;

it( 'round trips every fluent setter and getter', function()
{
	$product = ( new fixtureProduct() )
		->setId( 'sku-1' )
		->setName( 'Chew Toy' )
		->setShortDesc( 'Squeaky' )
		->setImgLoc( '/img/toy.png' )
		->setCategory( 'Toys' )
		->setPrice( '7.50' )
		->setTotalTypeIdentifier( 'price' );

	expect( $product->getId() )->toBe( 'sku-1' );
	expect( $product->getName() )->toBe( 'Chew Toy' );
	expect( $product->getShortDesc() )->toBe( 'Squeaky' );
	expect( $product->getImgLoc() )->toBe( '/img/toy.png' );
	expect( $product->getCategory() )->toBe( 'Toys' );
	expect( $product->getPrice() )->toBe( '7.50' );
	expect( $product->getTotalTypeIdentifier() )->toBe( 'price' );
} );

it( 'renders a name header with the default and a custom tag', function()
{
	$product = makeProduct( 'sku-1', '7.50' )->setName( 'Chew Toy' );

	expect( $product->getNameHeader() )
		->toBe( '<h4 data-name="product_name">Chew Toy</h4>' );

	expect( $product->getNameHeader( 'h2' ) )
		->toBe( '<h2 data-name="product_name">Chew Toy</h2>' );
} );

it( 'renders an image tag', function()
{
	$product = makeProduct( 'sku-1', '7.50' )
		->setName( 'Chew Toy' )
		->setImgLoc( '/img/toy.png' );

	expect( $product->getImageTag() )->toBe(
		'<img class="productImage" data-name="product_image" src="/img/toy.png" alt="Picture of Chew Toy">'
	);
} );

it( 'reports a base product as not serialized', function()
{
	expect( makeProduct( 'sku-1', '7.50' )->isSerialized() )->toBeFalse();
} );

it( 'returns its price for the price total type', function()
{
	$product = makeProduct( 'sku-1', '7.50' );

	expect( $product->getAmountForCatalogTotalType( priceType() ) )->toBe( '7.50' );
} );

it( 'throws for a total type the base product does not handle', function()
{
	// a product-field type the fixture does not special-case falls through
	// to product::getAmountForCatalogTotalType(), which rejects non-price types
	$weightType = ( new catalogTotalType( catalogTotalType::tPRODUCT_FIELD ) )
		->setIdentifier( 'weight' )
		->setProductField( 'weight' );

	$product = makeProduct( 'sku-1', '7.50' );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::unknownTypeErrorCode );

	$product->getAmountForCatalogTotalType( $weightType );
} );

it( 'delegates formatAmount to its formatter', function()
{
	$product = makeProduct( 'sku-1', '19.99' );
	$product->setFormatter( new productAmountFormatterPrice() );

	expect( $product->formatAmount( productAmountFormatterPrice::tPRICE ) )
		->toBe( '$ <span class="number">19.99</span>' );
} );

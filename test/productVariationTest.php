<?php

use treehousetim\shopCart\catalog;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\productVariation;

it( 'lets a variation override the parent price', function()
{
	$parent = makeProduct( 'shirt', '20.00' );

	$variation = ( new productVariation( $parent ) )
		->setAttribute( 'size', 'XL' )
		->setId( 'shirt-xl' )
		->setPrice( '24.50' );

	expect( $variation->getPrice() )->toBe( '24.50' );
	expect( $variation->getAmountForCatalogTotalType( priceType() ) )->toBe( '24.50' );
} );

it( 'falls back to the parent price when unset', function()
{
	$parent = makeProduct( 'shirt', '20.00' );

	$variation = ( new productVariation( $parent ) )
		->setAttribute( 'size', 'M' )
		->setId( 'shirt-m' );

	expect( $variation->getPrice() )->toBe( '20.00' );
	expect( $variation->getAmountForCatalogTotalType( priceType() ) )->toBe( '20.00' );
} );

it( 'stores and reports attributes', function()
{
	$parent = makeProduct( 'shirt', '20.00' );

	$variation = ( new productVariation( $parent ) )
		->setAttribute( 'size', 'L' )
		->setAttribute( 'color', 'blue' )
		->setId( 'shirt-l-blue' );

	expect( $variation->hasAttribute( 'size' ) )->toBeTrue();
	expect( $variation->hasAttribute( 'color' ) )->toBeTrue();
	expect( $variation->hasAttribute( 'material' ) )->toBeFalse();
	expect( $variation->getAttribute( 'size' ) )->toBe( 'L' );
	expect( $variation->getAttribute( 'color' ) )->toBe( 'blue' );
	expect( $variation->getAttributes() )->toBe( [ 'size' => 'L', 'color' => 'blue' ] );
} );

it( 'throws when reading a missing attribute', function()
{
	$parent = makeProduct( 'shirt', '20.00' );
	$variation = ( new productVariation( $parent ) )->setId( 'shirt-v' );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::noSuchAttributeErrorCode );

	$variation->getAttribute( 'size' );
} );

it( 'falls back to parent getters', function()
{
	$parent = makeProduct( 'shirt', '20.00' );
	$parent->setImgLoc( '/img/shirt.png' )->setCategory( 'apparel' );

	$variation = ( new productVariation( $parent ) )->setId( 'shirt-v' );

	expect( $variation->getName() )->toBe( 'Product shirt' );
	expect( $variation->getShortDesc() )->toBe( 'Description of shirt' );
	expect( $variation->getImgLoc() )->toBe( '/img/shirt.png' );
	expect( $variation->getCategory() )->toBe( 'apparel' );
	expect( $variation->getTotalTypeIdentifier() )->toBe( 'price' );
	expect( $variation->getParentProduct() )->toBe( $parent );

	$variation->setName( 'Shirt (Large, Blue)' );
	expect( $variation->getName() )->toBe( 'Shirt (Large, Blue)' );
} );

it( 'overrides a field amount and otherwise falls back to the parent', function()
{
	$parent = makeProduct( 'shirt', '20.00', '4' );

	$withOverride = ( new productVariation( $parent ) )
		->setFieldAmount( 'points', '9' )
		->setId( 'shirt-a' );

	$withoutOverride = ( new productVariation( $parent ) )->setId( 'shirt-b' );

	expect( $withOverride->hasFieldAmount( 'points' ) )->toBeTrue();
	expect( $withOverride->getFieldAmount( 'points' ) )->toBe( '9' );
	expect( $withOverride->getAmountForCatalogTotalType( pointsType() ) )->toBe( '9' );

	expect( $withoutOverride->hasFieldAmount( 'points' ) )->toBeFalse();
	expect( $withoutOverride->getAmountForCatalogTotalType( pointsType() ) )->toBe( '4' );
} );

it( 'throws when reading a missing field amount', function()
{
	$parent = makeProduct( 'shirt', '20.00' );
	$variation = ( new productVariation( $parent ) )->setId( 'shirt-v' );

	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::noSuchFieldAmountErrorCode );

	$variation->getFieldAmount( 'points' );
} );

it( 'resolves a variation in the catalog by its own id', function()
{
	$parent = makeProduct( 'shirt', '20.00' );

	$variation = ( new productVariation( $parent ) )
		->setAttribute( 'size', 'S' )
		->setId( 'shirt-s' );

	$catalog = ( new catalog() )
		->addProduct( $parent )
		->addProduct( $variation );

	expect( $catalog->hasProductId( 'shirt' ) )->toBeTrue();
	expect( $catalog->hasProductId( 'shirt-s' ) )->toBeTrue();
	expect( $catalog->getProductById( 'shirt-s' ) )->toBe( $variation );
	expect( $catalog->getProductById( 'shirt' ) )->toBe( $parent );
} );

it( 'reports a plain variation as not serialized', function()
{
	$parent = makeProduct( 'shirt', '20.00' );
	$variation = ( new productVariation( $parent ) )->setId( 'shirt-v' );

	expect( $parent->isSerialized() )->toBeFalse();
	expect( $variation->isSerialized() )->toBeFalse();
} );

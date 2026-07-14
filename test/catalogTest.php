<?php

use treehousetim\shopCart\catalog;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\test\fixtureCatalogLoader;

it( 'adds and retrieves products keyed by id', function()
{
	$a = makeProduct( 'a', '1.00' );
	$b = makeProduct( 'b', '2.00' );

	$catalog = ( new catalog() )->addProduct( $a )->addProduct( $b );

	expect( $catalog->hasProductId( 'a' ) )->toBeTrue();
	expect( $catalog->hasProductId( 'b' ) )->toBeTrue();
	expect( $catalog->hasProductId( 'missing' ) )->toBeFalse();
	expect( $catalog->getProductById( 'a' ) )->toBe( $a );
	expect( $catalog->getProducts() )->toBe( [ 'a' => $a, 'b' => $b ] );
} );

it( 'overwrites a product added under the same id', function()
{
	$first = makeProduct( 'a', '1.00' );
	$second = makeProduct( 'a', '9.00' );

	$catalog = ( new catalog() )->addProduct( $first )->addProduct( $second );

	expect( $catalog->getProductById( 'a' ) )->toBe( $second );
	expect( $catalog->getProducts() )->toHaveCount( 1 );
} );

it( 'throws when a product id is not found', function()
{
	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::notItemErrorCode );

	( new catalog() )->getProductById( 'nope' );
} );

it( 'populates from a loader', function()
{
	$a = makeProduct( 'a', '1.00' );
	$b = makeProduct( 'b', '2.00' );

	$catalog = ( new catalog() )
		->setProductLoader( new fixtureCatalogLoader( [ $a, $b ] ) )
		->populate();

	expect( $catalog->getProducts() )->toBe( [ 'a' => $a, 'b' => $b ] );
} );

it( 'adds nothing when the loader is empty', function()
{
	$catalog = ( new catalog() )
		->setProductLoader( new fixtureCatalogLoader( [] ) )
		->populate();

	expect( $catalog->getProducts() )->toBe( [] );
} );

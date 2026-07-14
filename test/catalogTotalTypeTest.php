<?php

use treehousetim\shopCart\catalogTotalType;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\test\fixtureTotalFormatter;

it( 'accepts the price and product-field types', function()
{
	expect( ( new catalogTotalType( catalogTotalType::tPRODUCT_PRICE ) )->getType() )
		->toBe( catalogTotalType::tPRODUCT_PRICE );

	expect( ( new catalogTotalType( catalogTotalType::tPRODUCT_FIELD ) )->getType() )
		->toBe( catalogTotalType::tPRODUCT_FIELD );
} );

it( 'throws for an unknown type', function()
{
	$this->expectException( Exception::class );
	$this->expectExceptionCode( Exception::unknownTypeErrorCode );

	new catalogTotalType( 'bogus' );
} );

it( 'round trips its setters and getters', function()
{
	$type = ( new catalogTotalType( catalogTotalType::tPRODUCT_FIELD ) )
		->setUnit( 'g' )
		->setLabel( 'Weight' )
		->setProductField( 'weight' )
		->setIdentifier( 'weight-id' );

	expect( $type->getUnit() )->toBe( 'g' );
	expect( $type->getLabel() )->toBe( 'Weight' );
	expect( $type->getProductField() )->toBe( 'weight' );
	expect( $type->getIdentifier() )->toBe( 'weight-id' );
} );

it( 'defaults the label to an empty string', function()
{
	expect( ( new catalogTotalType( catalogTotalType::tPRODUCT_PRICE ) )->getLabel() )->toBe( '' );
} );

it( 'delegates format() to its formatter and returns the value', function()
{
	$type = ( new catalogTotalType( catalogTotalType::tPRODUCT_PRICE ) )
		->setIdentifier( 'price' )
		->setFormatter( new fixtureTotalFormatter() );

	expect( $type->format( '50.00' ) )->toBe( 'price:50.00' );
} );

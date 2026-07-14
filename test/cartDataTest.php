<?php

use treehousetim\shopCart\test\fixtureCartData;

it( 'round trips data and type', function()
{
	$data = ( new fixtureCartData() )
		->setType( 'coupon' )
		->setData( [ 'code' => 'SAVE10' ] );

	expect( $data->getType() )->toBe( 'coupon' );
	expect( $data->getData() )->toBe( [ 'code' => 'SAVE10' ] );
} );

it( 'returns its serialized form for storage', function()
{
	$data = ( new fixtureCartData() )->setData( [ 'code' => 'SAVE10' ] );

	// getForStorage() delegates to jsonSerialize(), which returns the data
	expect( $data->getForStorage() )->toBe( [ 'code' => 'SAVE10' ] );
	expect( $data->getForStorage() )->toBe( $data->jsonSerialize() );
} );

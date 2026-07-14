<?php

use treehousetim\shopCart\Exception;

it( 'sets message and integer code', function()
{
	$e = new Exception( 'boom', Exception::duplicateSerialErrorCode );

	expect( $e->getMessage() )->toBe( 'boom' );
	expect( $e->getCode() )->toBe( Exception::duplicateSerialErrorCode );
} );

it( 'joins multiple message parts with newlines', function()
{
	$e = new Exception( 'first', 'second', 5 );

	expect( $e->getMessage() )->toBe( "first" . PHP_EOL . "second" );
	expect( $e->getCode() )->toBe( 5 );
} );

it( 'falls back to the undefined code when no integer is passed', function()
{
	$e = new Exception( 'just a message' );

	expect( $e->getMessage() )->toBe( 'just a message' );
	expect( $e->getCode() )->toBe( Exception::undefined );
} );

it( 'is a LogicException', function()
{
	expect( new Exception( 'x', 1 ) )->toBeInstanceOf( \LogicException::class );
} );

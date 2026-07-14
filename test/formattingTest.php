<?php

use treehousetim\shopCart\formatting;

it( 'formats a long number to six decimal places', function()
{
	expect( formatting::longNumberFormat( 3 ) )->toBe( '3.000000' );
	expect( formatting::longNumberFormat( 2.5 ) )->toBe( '2.500000' );
	expect( formatting::longNumberFormat( '19.99' ) )->toBe( '19.990000' );
} );

it( 'appends a unit to a long-formatted number', function()
{
	expect( formatting::unitFormat( 2.5, 'kg' ) )->toBe( '2.500000 kg' );
} );

it( 'formats money with sign and span markup', function()
{
	expect( formatting::moneyFormat( 19.99 ) )
		->toBe( '$ <span class="number">19.99</span>' );

	expect( formatting::moneyFormat( 1234.5 ) )
		->toBe( '$ <span class="number">1,234.50</span>' );
} );

it( 'moves the minus sign outside the dollar sign for negatives', function()
{
	expect( formatting::moneyFormat( -5 ) )
		->toBe( '-$ <span class="number">5.00</span>' );
} );

it( 'honours a custom decimals argument in moneyFormat', function()
{
	expect( formatting::moneyFormat( 10, 0 ) )
		->toBe( '$ <span class="number">10</span>' );
} );

it( 'keeps significant zeros in unitFormatAutoScale', function()
{
	// regression: the old trim( $n, " -0" ) turned "100" into "1"
	expect( formatting::unitFormatAutoScale( '100', 'g' ) )
		->toBe( '<span class="number">100</span> <span class="units">g</span>' );
} );

it( 'trims trailing zeros only after a decimal point', function()
{
	expect( formatting::unitFormatAutoScale( '0.500000', 'g' ) )
		->toBe( '<span class="number">0.5</span> <span class="units">g</span>' );

	expect( formatting::unitFormatAutoScale( '10.00', 'u' ) )
		->toBe( '<span class="number">10</span> <span class="units">u</span>' );
} );

it( 'keeps the sign and produces a well-formed closing tag for negatives', function()
{
	expect( formatting::unitFormatAutoScale( '-5.50', 'kg' ) )
		->toBe( '<span class="number">-5.5</span> <span class="units">kg</span>' );
} );

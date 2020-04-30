<?php

function longNumberFormat( $number )
{
	return number_format( $number, 6 );
}
//------------------------------------------------------------------------
function unitFormat( $number, $unit )
{
	return longNumberFormat( $number ) . ' ' . $unit;
}
//------------------------------------------------------------------------
function unitFormatAutoScale( $number, $unit )
{
	$sign = '';
	if( $number < 0 )
	{
		$sign = '-';
	}

	return '<span class="number">' . $sign . trim( $number, ' -0' ) . '</span> <span class="units">' . $unit . '</unit';
}
//------------------------------------------------------------------------
function moneyFormat( $number )
{
	$sign = '';
	if( $number < 0 )
	{
		$sign = '-';
	}

	return $sign . '$<span class="number">' . trim( number_format( $number, 2 ), ' -' ) . '</span>';
}
//------------------------------------------------------------------------
function formatItemWeight( $item )
{
	return unitFormatAutoScale( $item['weight'], $item['unit'] );
}
//------------------------------------------------------------------------
function formatItemCost( $item )
{
	return moneyFormat( $item['premium'] );
}
//------------------------------------------------------------------------
function formatItemTotalWeight( $item )
{
	return unitFormatAutoScale( getItemTotalWeight( $item ), $item['unit'] );
}
function formatItemTotalPremium( $item )
{
	 return moneyFormat( getItemTotalPremium( $item ) );
}
//------------------------------------------------------------------------
function getItemTotalWeight( $item )
{
	return bcmul( $item['weight'] , $item['qty'], 6 );
}
//------------------------------------------------------------------------
function getItemTotalPremium( $item )
{
	return bcmul( $item['premium'], $item['qty'], 6 );
}
//------------------------------------------------------------------------
function cartAccumulateTotal( &$total, $item )
{
	$metal = $item['metal'];

	$total[$metal]['weight'] = bcadd( getItemTotalWeight( $item ), $total[$metal]['weight'], 6 );
	$total[$metal]['premium'] = bcadd( getItemTotalPremium( $item ), $total[$metal]['premium'], 6 );
	$total[$metal]['unit'] = $item['unit'];
	$total['all']['premium'] = bcadd( $total['all']['premium']??0, getItemTotalPremium( $item ), 6 );
}
//------------------------------------------------------------------------
function getItemQty( $item )
{
	return $item['qty'];
}
//------------------------------------------------------------------------
function formatHoldingType( $type )
{
	return ucfirst( strtolower( $type ) );
}
//------------------------------------------------------------------------
function formatTotalWeight( $type, $total )
{
	$totalRecord = $total[$type];

	return unitFormatAutoScale( $totalRecord['weight'], $totalRecord['unit'] );
}
//------------------------------------------------------------------------
function formatTotalPremium( $total )
{
	return moneyFormat( $total['all']['premium'] );
}
<?php namespace treehousetim\shopCart;

class formatting
{
	public static $longScale = 6;
	public static function longNumberFormat( $number )
	{
		return number_format( $number, self::longScale );
	}
	//------------------------------------------------------------------------
	public static function unitFormat( $number, $unit )
	{
		return longNumberFormat( $number ) . ' ' . $unit;
	}
	//------------------------------------------------------------------------
	public static function unitFormatAutoScale( $number, $unit )
	{
		$sign = '';
		if( $number < 0 )
		{
			$sign = '-';
		}

		return '<span class="number">' . $sign . trim( $number, ' -0' ) . '</span> <span class="units">' . $unit . '</unit';
	}
	//------------------------------------------------------------------------
	public static function moneyFormat( $number, $decimals = 2 )
	{
		$sign = '';
		if( $number < 0 )
		{
			$sign = '-';
		}

		return $sign . '$ <span class="number">' . trim( number_format( $number, $decimals ), ' -' ) . '</span>';
	}
}
<?php namespace treehousetim\shopCart;

class formatting
{
	public static $longScale = 6;
	public static function longNumberFormat( $number )
	{
		return number_format( $number, self::$longScale );
	}
	//------------------------------------------------------------------------
	public static function unitFormat( $number, $unit )
	{
		return self::longNumberFormat( $number ) . ' ' . $unit;
	}
	//------------------------------------------------------------------------
	public static function unitFormatAutoScale( $number, $unit )
	{
		$sign = '';
		if( $number < 0 )
		{
			$sign = '-';
		}

		$number = ltrim( $number, ' -' );

		// only trim insignificant trailing zeros after a decimal point
		if( strpos( $number, '.' ) !== false )
		{
			$number = rtrim( rtrim( $number, '0' ), '.' );
		}

		return '<span class="number">' . $sign . $number . '</span> <span class="units">' . $unit . '</span>';
	}
	//------------------------------------------------------------------------
	public static function moneyFormat( float $number, $decimals = 2 )
	{
		$sign = '';
		if( $number < 0 )
		{
			$sign = '-';
		}

		return $sign . '$ <span class="number">' . trim( number_format( $number, $decimals ), ' -' ) . '</span>';
	}
}
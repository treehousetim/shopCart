<?php namespace treehousetim\shopCart;

class Exception extends \LogicException
{
	const undefined = -1;

	const notItemErrorCode = 0;
	const invalidDataTypeErrorCode = 1;
	const invalidFormatErrorCode = 3;
	const noActiveSessionErrorCode = 4;
	const unknownTypeErrorCode = 5;
	const noSuchAttributeErrorCode = 6;
	const duplicateSerialErrorCode = 7;
	const serializedQtyErrorCode = 8;
	const noSuchFieldAmountErrorCode = 9;

	//------------------------------------------------------------------------
	// variadic constructor: pass any number of message parts followed by an
	// optional integer error code. If the last argument is an integer it is
	// used as the code; otherwise it is treated as the final message part and
	// the code defaults to self::undefined.
	public function __construct( ...$message )
	{
		$lastValue = array_pop( $message );

		if( (string)intval( $lastValue ) == $lastValue )
		{
			$code = $lastValue;
		}
		else
		{
			$message[] = $lastValue;
			$code = self::undefined;
		}

		parent::__construct( implode( PHP_EOL, $message ), $code );
	}
}

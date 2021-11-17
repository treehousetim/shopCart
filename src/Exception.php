<?php namespace treehousetim\shopCart;

class Exception extends \treehousetim\exception\Exception 
{
	const notItemErrorCode = 0;
	const invalidDataTypeErrorCode = 1;
	const invalidFormatErrorCode = 3;
	const noActiveSessionErrorCode = 4;
	const unknownTypeErrorCode = 5;
}
<?php namespace treehousetim\shopCart;
use \stdClass, \Exception, \ReflectionClass;
class cartData
{
	const tOBJ = 'object';
	const tARR = 'array';
	const tMetalAllocation = 'metalAllocation';
	const tCharges = 'Charges';
	const tPaymentMethod = 'paymentMethod';


	protected $data;
	protected $type;

	//------------------------------------------------------------------------
	public function setType( string $type )
	{
		switch( $type )
		{
		case self::tOBJ:
		case self::tARR:
		case self::tMetalAllocation:
		case self::tCharges:
		case self::tPaymentMethod:
			$this->type = $type;
			break;

		default:
			throw new Exception( 'Unknown type: ' . $type );
		}

		return $this;
	}
	//------------------------------------------------------------------------
	static function getConstants() {
        $oClass = new ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }
	//------------------------------------------------------------------------
	public function isValidType( string $type ): bool
	{
		return in_array($type, self::getConstants());
	}
	//------------------------------------------------------------------------
	//for now we are not planning to use this and use setDataArray for now.
	public function setDataObject( object $data ) : self
	{
		throw new Exception("Use setDataArray instead.", 1);
		
		$this->setType( self::tOBJ );
		$this->data = $data;
		return $this;
	}
	//------------------------------------------------------------------------
	public function setData( array $data ) : self
	{
		//$this->setType( self::tARR );
		$this->data = $data;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getDataObject() : object
	{
		return (object)$this->data;
	}
	//------------------------------------------------------------------------
	public function getDataArray() : array
	{
		return (array)$this->data;
	}

	public function getType()
	{
		return $this->type;
	}
}
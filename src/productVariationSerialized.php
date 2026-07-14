<?php namespace treehousetim\shopCart;

class productVariationSerialized extends productVariation
{
	protected $serialNumber;

	public function setSerialNumber( string $serialNumber ) : self
	{
		$this->serialNumber = $serialNumber;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getSerialNumber() : string
	{
		return $this->serialNumber;
	}
	//------------------------------------------------------------------------
	public function isSerialized() : bool
	{
		return true;
	}
	//------------------------------------------------------------------------
	// when no id has been set explicitly, the id is derived from the parent
	// product id and the serial number so every serialized unit is
	// resolvable from the catalog by a unique id.
	//------------------------------------------------------------------------
	public function getId() : string
	{
		if( $this->id !== null )
		{
			return $this->id;
		}

		return $this->parentProduct->getId() . ':' . $this->serialNumber;
	}
}

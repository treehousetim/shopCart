<?php namespace treehousetim\shopCart;

abstract class cartData implements \JsonSerializable, iCartData
{
	protected $data;
	protected $type;

	//------------------------------------------------------------------------
	#[\ReturnTypeWillChange]
	abstract public function jsonSerialize();
	//------------------------------------------------------------------------
	public function getForStorage()
	{
		return $this->jsonSerialize();
	}
	//------------------------------------------------------------------------
	// return iCartData (not self) to stay invariant with the interface:
	// covariant return types are a fatal error before PHP 7.4
	public function setData( $data ) : iCartData
	{
		$this->data = $data;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getData()
	{
		return $this->data;
	}
	//------------------------------------------------------------------------
	public function setType( string $type ) : iCartData
	{
		$this->type = $type;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getType()
	{
		return $this->type;
	}
}
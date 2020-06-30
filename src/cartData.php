<?php namespace treehousetim\shopCart;
use \stdClass, \Exception, \ReflectionClass;

abstract class cartData implements jsonSerializable
{
	protected $data;
	protected $type;

	//------------------------------------------------------------------------
	abstract public function jsonSerialize();
	//------------------------------------------------------------------------
	public function getForStorage()
	{
		return $this->jsonSerialize();
	}
	//------------------------------------------------------------------------
	public function setData( $data ) : self
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
	public function setType( string $type ) : self
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
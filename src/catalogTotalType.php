<?php namespace treehousetim\shopCart;

class catalogTotalType
{
	const tPRODUCT_FIELD = 'product';
	const tPRODUCT_PRICE = 'price';

	protected $type = self::tPRODUCT_PRICE;
	protected $unit;
	protected $productField;
	protected $formatter;
	protected $label = '';
	protected $identifier;

	//------------------------------------------------------------------------
	public function __construct( string $type )
	{
		switch( $type )
		{
		case self::tPRODUCT_FIELD:
		case self::tPRODUCT_PRICE:
			$this->type = $type;
			break;

		default:
			throw new Exception( 'Unknown type: ' . $type, Exception::unknownTypeErrorCode );
		}
	}
	//------------------------------------------------------------------------
	public function getType() : string
	{
		return $this->type;
	}
	//------------------------------------------------------------------------
	public function setUnit( string $value ) : self
	{
		$this->unit = $value;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getUnit() : string
	{
		return $this->unit;
	}
	//------------------------------------------------------------------------
	public function setLabel( string $value ) : self
	{
		$this->label = $value;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getLabel() : string
	{
		return $this->label;
	}
	//------------------------------------------------------------------------
	public function setProductField( string $value ) : self
	{
		$this->productField = $value;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getProductField() : string
	{
		return $this->productField;
	} 
	//------------------------------------------------------------------------
	public function setFormatter( shopCartTotalFormatterInterface $formatter ) : self
	{
		$this->formatter = $formatter;
		return $this;
	}
	//------------------------------------------------------------------------
	public function format()
	{
		$this->formatter->formatTotalType( $this );
	}
	//------------------------------------------------------------------------
	public function setIdentifier( string $id ) : self
	{
		$this->identifier = $id;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getIdentifier() : string
	{
		return $this->identifier;
	}
}
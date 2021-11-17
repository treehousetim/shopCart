<?php namespace treehousetim\shopCart;

abstract class product
{
	protected $id;
	protected $name;
	protected $shortDesc;
	protected $imgLoc;
	protected $price;
	protected $category;
	protected $formatter;
	protected $totalTypeIdentifier;

	//------------------------------------------------------------------------
	public function getNameHeader( $tag = 'h4' )
	{
		return '<' . $tag . ' data-name="product_name">' . $this->getName() . '</' . $tag . '>';
	}
	//------------------------------------------------------------------------
	public function getImageTag()
	{
		return '<img class="productImage" data-name="product_image" src="' . $this->getImgLoc() . '" alt="Picture of ' . $this->getName() . '">';
	}
	//------------------------------------------------------------------------
	public function formatAmount( string $type ) : string
	{
		return $this->formatter->formatProduct( $type, $this );
	}
	//------------------------------------------------------------------------
	// getters and setters below
	//------------------------------------------------------------------------
	public function setFormatter( productAmountFormatter $formatter )
	{
	 	$this->formatter = $formatter;
	}
	//------------------------------------------------------------------------
	public function getFormatter() : productAmountFormatter
	{
		return $this->formatter;
	}
	//------------------------------------------------------------------------
	public function getPrice() : string
	{
		return $this->price;
	}
	//------------------------------------------------------------------------
	public function getId() : string
	{
		return $this->id;
	}
	//------------------------------------------------------------------------
	public function getName() : string
	{
		return $this->name;
	}
	//------------------------------------------------------------------------
	public function getShortDesc() : string
	{
		return $this->shortDesc;
	}
	//------------------------------------------------------------------------
	public function getImgLoc() : string
	{
		return $this->imgLoc;
	}
	//------------------------------------------------------------------------
	public function getCategory() : string
	{
		return $this->category;
	}
	//------------------------------------------------------------------------
	public function setId( string $id ) : self
	{
		$this->id = $id;
		return $this;
	}
	//------------------------------------------------------------------------
	public function setName( string $name ) : self
	{
		$this->name = $name;
		return $this;
	}
	//------------------------------------------------------------------------
	public function setShortDesc( string $shortDesc ) : self
	{
		$this->shortDesc = $shortDesc;
		return $this;
	}
	//------------------------------------------------------------------------
	public function setImgLoc( string $imgLoc ) : self
	{
		$this->imgLoc = $imgLoc;
		return $this;
	}
	//------------------------------------------------------------------------
	public function setCategory( string $category ) : self
	{
		$this->category = $category;
		return $this;
	}
	//------------------------------------------------------------------------
	public function setPrice( string $price ) : self
	{
		$this->price = $price;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getTotalTypeIdentifier() : string
	{
		return $this->totalTypeIdentifier;
	}
	//------------------------------------------------------------------------
	public function setTotalTypeIdentifier( string $value ) : self
	{
		$this->totalTypeIdentifier = $value;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getAmountForCatalogTotalType( catalogTotalType $type ) : string
	{
		if( $type->getType() == catalogTotalType::tPRODUCT_PRICE )
		{
			return $this->price;
		}

		throw new Exception( 'Unknown catalog total type: ' . $type->getType(), Exception::unknownTypeErrorCode );
	}
}
<?php namespace treehousetim\shopCart;

class productVariation extends product
{
	protected $parentProduct;
	protected $attributes = [];
	protected $fieldAmounts = [];

	public function __construct( product $parentProduct )
	{
		$this->parentProduct = $parentProduct;
	}
	//------------------------------------------------------------------------
	public function getParentProduct() : product
	{
		return $this->parentProduct;
	}
	//------------------------------------------------------------------------
	public function setAttribute( string $name, string $value ) : self
	{
		$this->attributes[$name] = $value;
		return $this;
	}
	//------------------------------------------------------------------------
	public function hasAttribute( string $name ) : bool
	{
		return array_key_exists( $name, $this->attributes );
	}
	//------------------------------------------------------------------------
	public function getAttribute( string $name ) : string
	{
		if( ! $this->hasAttribute( $name ) )
		{
			throw new Exception( 'No such attribute: ' . $name, Exception::noSuchAttributeErrorCode );
		}

		return $this->attributes[$name];
	}
	//------------------------------------------------------------------------
	public function getAttributes() : array
	{
		return $this->attributes;
	}
	//------------------------------------------------------------------------
	public function setFieldAmount( string $field, string $amount ) : self
	{
		$this->fieldAmounts[$field] = $amount;
		return $this;
	}
	//------------------------------------------------------------------------
	public function hasFieldAmount( string $field ) : bool
	{
		return array_key_exists( $field, $this->fieldAmounts );
	}
	//------------------------------------------------------------------------
	public function getFieldAmount( string $field ) : string
	{
		if( ! $this->hasFieldAmount( $field ) )
		{
			throw new Exception( 'No such field amount: ' . $field, Exception::noSuchFieldAmountErrorCode );
		}

		return $this->fieldAmounts[$field];
	}
	//------------------------------------------------------------------------
	// getters fall back to the parent product when no override has been set
	//------------------------------------------------------------------------
	public function getPrice() : string
	{
		if( $this->price !== null )
		{
			return $this->price;
		}

		return $this->parentProduct->getPrice();
	}
	//------------------------------------------------------------------------
	public function getName() : string
	{
		if( $this->name !== null )
		{
			return $this->name;
		}

		return $this->parentProduct->getName();
	}
	//------------------------------------------------------------------------
	public function getShortDesc() : string
	{
		if( $this->shortDesc !== null )
		{
			return $this->shortDesc;
		}

		return $this->parentProduct->getShortDesc();
	}
	//------------------------------------------------------------------------
	public function getImgLoc() : string
	{
		if( $this->imgLoc !== null )
		{
			return $this->imgLoc;
		}

		return $this->parentProduct->getImgLoc();
	}
	//------------------------------------------------------------------------
	public function getCategory() : string
	{
		if( $this->category !== null )
		{
			return $this->category;
		}

		return $this->parentProduct->getCategory();
	}
	//------------------------------------------------------------------------
	public function getFormatter() : productAmountFormatter
	{
		if( $this->formatter !== null )
		{
			return $this->formatter;
		}

		return $this->parentProduct->getFormatter();
	}
	//------------------------------------------------------------------------
	public function getTotalTypeIdentifier() : string
	{
		if( $this->totalTypeIdentifier !== null )
		{
			return $this->totalTypeIdentifier;
		}

		return $this->parentProduct->getTotalTypeIdentifier();
	}
	//------------------------------------------------------------------------
	public function getAmountForCatalogTotalType( catalogTotalType $type ) : string
	{
		if( $type->getType() == catalogTotalType::tPRODUCT_PRICE )
		{
			return $this->getPrice();
		}

		if( $type->getType() == catalogTotalType::tPRODUCT_FIELD && $this->hasFieldAmount( $type->getProductField() ) )
		{
			return $this->fieldAmounts[ $type->getProductField() ];
		}

		return $this->parentProduct->getAmountForCatalogTotalType( $type );
	}
}

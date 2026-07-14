<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\product;
use treehousetim\shopCart\catalogTotalType;

class fixtureProduct extends product
{
	protected $points = '0';

	public function setPoints( string $points ) : self
	{
		$this->points = $points;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getPoints() : string
	{
		return $this->points;
	}
	//------------------------------------------------------------------------
	public function getAmountForCatalogTotalType( catalogTotalType $type ) : string
	{
		if( $type->getType() == catalogTotalType::tPRODUCT_FIELD && $type->getProductField() == 'points' )
		{
			return $this->points;
		}

		return parent::getAmountForCatalogTotalType( $type );
	}
}

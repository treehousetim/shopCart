<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\catalog;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\productVariation;

class productVariationTest extends testBase
{
	public function testPriceOverride()
	{
		$parent = $this->makeProduct( 'shirt', '20.00' );

		$variation = ( new productVariation( $parent ) )
			->setAttribute( 'size', 'XL' )
			->setId( 'shirt-xl' )
			->setPrice( '24.50' );

		$this->assertSame( '24.50', $variation->getPrice() );
		$this->assertSame( '24.50', $variation->getAmountForCatalogTotalType( $this->priceType() ) );
	}
	//------------------------------------------------------------------------
	public function testPriceDefaultsToParent()
	{
		$parent = $this->makeProduct( 'shirt', '20.00' );

		$variation = ( new productVariation( $parent ) )
			->setAttribute( 'size', 'M' )
			->setId( 'shirt-m' );

		$this->assertSame( '20.00', $variation->getPrice() );
		$this->assertSame( '20.00', $variation->getAmountForCatalogTotalType( $this->priceType() ) );
	}
	//------------------------------------------------------------------------
	public function testAttributes()
	{
		$parent = $this->makeProduct( 'shirt', '20.00' );

		$variation = ( new productVariation( $parent ) )
			->setAttribute( 'size', 'L' )
			->setAttribute( 'color', 'blue' )
			->setId( 'shirt-l-blue' );

		$this->assertTrue( $variation->hasAttribute( 'size' ) );
		$this->assertTrue( $variation->hasAttribute( 'color' ) );
		$this->assertFalse( $variation->hasAttribute( 'material' ) );
		$this->assertSame( 'L', $variation->getAttribute( 'size' ) );
		$this->assertSame( 'blue', $variation->getAttribute( 'color' ) );
		$this->assertSame( ['size' => 'L', 'color' => 'blue'], $variation->getAttributes() );
	}
	//------------------------------------------------------------------------
	public function testMissingAttributeThrows()
	{
		$parent = $this->makeProduct( 'shirt', '20.00' );
		$variation = ( new productVariation( $parent ) )->setId( 'shirt-v' );

		$this->expectException( Exception::class );
		$this->expectExceptionCode( Exception::noSuchAttributeErrorCode );

		$variation->getAttribute( 'size' );
	}
	//------------------------------------------------------------------------
	public function testParentFallbackGetters()
	{
		$parent = $this->makeProduct( 'shirt', '20.00' );
		$parent->setImgLoc( '/img/shirt.png' )->setCategory( 'apparel' );

		$variation = ( new productVariation( $parent ) )->setId( 'shirt-v' );

		$this->assertSame( 'Product shirt', $variation->getName() );
		$this->assertSame( 'Description of shirt', $variation->getShortDesc() );
		$this->assertSame( '/img/shirt.png', $variation->getImgLoc() );
		$this->assertSame( 'apparel', $variation->getCategory() );
		$this->assertSame( 'price', $variation->getTotalTypeIdentifier() );
		$this->assertSame( $parent, $variation->getParentProduct() );

		$variation->setName( 'Shirt (Large, Blue)' );
		$this->assertSame( 'Shirt (Large, Blue)', $variation->getName() );
	}
	//------------------------------------------------------------------------
	public function testFieldAmountOverrideAndParentFallback()
	{
		$parent = $this->makeProduct( 'shirt', '20.00', '4' );

		$withOverride = ( new productVariation( $parent ) )
			->setFieldAmount( 'points', '9' )
			->setId( 'shirt-a' );

		$withoutOverride = ( new productVariation( $parent ) )->setId( 'shirt-b' );

		$this->assertTrue( $withOverride->hasFieldAmount( 'points' ) );
		$this->assertSame( '9', $withOverride->getFieldAmount( 'points' ) );
		$this->assertSame( '9', $withOverride->getAmountForCatalogTotalType( $this->pointsType() ) );

		$this->assertFalse( $withoutOverride->hasFieldAmount( 'points' ) );
		$this->assertSame( '4', $withoutOverride->getAmountForCatalogTotalType( $this->pointsType() ) );
	}
	//------------------------------------------------------------------------
	public function testMissingFieldAmountThrows()
	{
		$parent = $this->makeProduct( 'shirt', '20.00' );
		$variation = ( new productVariation( $parent ) )->setId( 'shirt-v' );

		$this->expectException( Exception::class );
		$this->expectExceptionCode( Exception::noSuchFieldAmountErrorCode );

		$variation->getFieldAmount( 'points' );
	}
	//------------------------------------------------------------------------
	public function testCatalogResolutionByVariationId()
	{
		$parent = $this->makeProduct( 'shirt', '20.00' );

		$variation = ( new productVariation( $parent ) )
			->setAttribute( 'size', 'S' )
			->setId( 'shirt-s' );

		$catalog = ( new catalog() )
			->addProduct( $parent )
			->addProduct( $variation );

		$this->assertTrue( $catalog->hasProductId( 'shirt' ) );
		$this->assertTrue( $catalog->hasProductId( 'shirt-s' ) );
		$this->assertSame( $variation, $catalog->getProductById( 'shirt-s' ) );
		$this->assertSame( $parent, $catalog->getProductById( 'shirt' ) );
	}
	//------------------------------------------------------------------------
	public function testIsNotSerialized()
	{
		$parent = $this->makeProduct( 'shirt', '20.00' );
		$variation = ( new productVariation( $parent ) )->setId( 'shirt-v' );

		$this->assertFalse( $parent->isSerialized() );
		$this->assertFalse( $variation->isSerialized() );
	}
}

<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\cart;
use treehousetim\shopCart\catalog;
use treehousetim\shopCart\Exception;
use treehousetim\shopCart\productVariationSerialized;

class productVariationSerializedTest extends testBase
{
	protected function makeSerialized( string $serial, $price = null, $points = null ) : productVariationSerialized
	{
		$parent = $this->makeProduct( 'widget', '10.00', '2' );

		$unit = ( new productVariationSerialized( $parent ) )
			->setSerialNumber( $serial );

		if( $price !== null )
		{
			$unit->setPrice( $price );
		}

		if( $points !== null )
		{
			$unit->setFieldAmount( 'points', $points );
		}

		return $unit;
	}
	//------------------------------------------------------------------------
	protected function makeCart( catalog $catalog ) : cart
	{
		return new cart( $catalog );
	}
	//------------------------------------------------------------------------
	public function testDerivedId()
	{
		$unit = $this->makeSerialized( 'SN-0001' );

		$this->assertSame( 'widget:SN-0001', $unit->getId() );
		$this->assertSame( 'SN-0001', $unit->getSerialNumber() );
		$this->assertTrue( $unit->isSerialized() );
	}
	//------------------------------------------------------------------------
	public function testExplicitIdWins()
	{
		$unit = $this->makeSerialized( 'SN-0001' )->setId( 'my-own-id' );

		$this->assertSame( 'my-own-id', $unit->getId() );
	}
	//------------------------------------------------------------------------
	public function testAddingSameSerialTwiceThrows()
	{
		$unit = $this->makeSerialized( 'SN-0001' );
		$cart = $this->makeCart( ( new catalog() )->addProduct( $unit ) );

		$cart->addProduct( $unit, 1 );
		$this->assertSame( 1, $cart->getItemByProductId( $unit->getId() )->getQty() );

		$this->expectException( Exception::class );
		$this->expectExceptionCode( Exception::duplicateSerialErrorCode );

		$cart->addProduct( $unit, 1 );
	}
	//------------------------------------------------------------------------
	public function testDuplicateAddDoesNotIncreaseQty()
	{
		$unit = $this->makeSerialized( 'SN-0001' );
		$cart = $this->makeCart( ( new catalog() )->addProduct( $unit ) );

		$cart->addProduct( $unit, 1 );

		try
		{
			$cart->addProduct( $unit, 1 );
			$this->fail( 'expected duplicate serial Exception' );
		}
		catch( Exception $e )
		{
			$this->assertSame( Exception::duplicateSerialErrorCode, $e->getCode() );
		}

		$this->assertSame( 1, $cart->getItemByProductId( $unit->getId() )->getQty() );
		$this->assertSame( '1', $cart->getTotalQty() );
	}
	//------------------------------------------------------------------------
	public function testAddingWithQtyOtherThanOneThrows()
	{
		$unit = $this->makeSerialized( 'SN-0001' );
		$cart = $this->makeCart( ( new catalog() )->addProduct( $unit ) );

		$this->expectException( Exception::class );
		$this->expectExceptionCode( Exception::serializedQtyErrorCode );

		$cart->addProduct( $unit, 2 );
	}
	//------------------------------------------------------------------------
	public function testUpdateItemQtyThrowsForSerialized()
	{
		$unit = $this->makeSerialized( 'SN-0001' );
		$cart = $this->makeCart( ( new catalog() )->addProduct( $unit ) );

		$cart->addProduct( $unit, 1 );

		// updating to 1 is a no-op and allowed
		$cart->updateItemQty( $unit->getId(), 1 );
		$this->assertSame( 1, $cart->getItemByProductId( $unit->getId() )->getQty() );

		$this->expectException( Exception::class );
		$this->expectExceptionCode( Exception::serializedQtyErrorCode );

		$cart->updateItemQty( $unit->getId(), 3 );
	}
	//------------------------------------------------------------------------
	public function testPerSerialAmountsPerTotalType()
	{
		$parent = $this->makeProduct( 'widget', '10.00', '2' );

		$unitA = ( new productVariationSerialized( $parent ) )
			->setSerialNumber( 'SN-A' )
			->setPrice( '12.345678' )
			->setFieldAmount( 'points', '7.5' );

		$unitB = ( new productVariationSerialized( $parent ) )
			->setSerialNumber( 'SN-B' )
			->setPrice( '11.111111' )
			->setFieldAmount( 'points', '3.25' );

		$priceType = $this->priceType();
		$pointsType = $this->pointsType();

		// two serials of the same parent product differ in both dimensions
		$this->assertSame( '12.345678', $unitA->getAmountForCatalogTotalType( $priceType ) );
		$this->assertSame( '7.5', $unitA->getAmountForCatalogTotalType( $pointsType ) );
		$this->assertSame( '11.111111', $unitB->getAmountForCatalogTotalType( $priceType ) );
		$this->assertSame( '3.25', $unitB->getAmountForCatalogTotalType( $pointsType ) );
	}
	//------------------------------------------------------------------------
	public function testPerSerialAmountsFeedCartTotals()
	{
		$parent = $this->makeProduct( 'widget', '10.00', '2' );

		$unitA = ( new productVariationSerialized( $parent ) )
			->setSerialNumber( 'SN-A' )
			->setPrice( '12.345678' )
			->setFieldAmount( 'points', '7.5' );

		$unitB = ( new productVariationSerialized( $parent ) )
			->setSerialNumber( 'SN-B' )
			->setPrice( '11.111111' )
			->setFieldAmount( 'points', '3.25' );

		$catalog = ( new catalog() )
			->addProduct( $parent )
			->addProduct( $unitA )
			->addProduct( $unitB );

		$cart = $this->makeCart( $catalog );
		$cart->addProduct( $parent, 3 );
		$cart->addProduct( $unitA, 1 );
		$cart->addProduct( $unitB, 1 );

		$priceType = $this->priceType();
		$pointsType = $this->pointsType();

		// 3 x 10.00 + 12.345678 + 11.111111 = 53.456789
		$this->assertSame( '53.456789', $cart->getAmountTotal( $priceType ) );

		// getTotal() only counts items whose totalTypeIdentifier matches;
		// serialized units fall back to the parent's identifier ('price')
		$this->assertSame( '53.456789', $cart->getTotal( $priceType ) );

		// 3 x 2 + 7.5 + 3.25 = 16.75
		$this->assertSame( '16.750000', $cart->getAmountTotal( $pointsType ) );
	}
	//------------------------------------------------------------------------
	public function testSerialWithoutOverridesUsesParentAmounts()
	{
		$unit = $this->makeSerialized( 'SN-PLAIN' );

		$this->assertSame( '10.00', $unit->getAmountForCatalogTotalType( $this->priceType() ) );
		$this->assertSame( '2', $unit->getAmountForCatalogTotalType( $this->pointsType() ) );
	}
}

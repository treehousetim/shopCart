<?php namespace treehousetim\shopCart\test;

use treehousetim\shopCart\cart;
use treehousetim\shopCart\catalog;
use treehousetim\shopCart\cartStorageSession;
use treehousetim\shopCart\productVariation;
use treehousetim\shopCart\productVariationSerialized;

/**
 * each test runs in its own process so session_start() happens before any
 * phpunit output has been sent
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class cartStorageSessionTest extends testBase
{
	protected function setUp() : void
	{
		if( session_status() != PHP_SESSION_ACTIVE )
		{
			session_start();
		}

		$_SESSION = [];
	}
	//------------------------------------------------------------------------
	protected function buildCatalog() : catalog
	{
		$parent = $this->makeProduct( 'shirt', '20.00', '4' );

		$variation = ( new productVariation( $parent ) )
			->setAttribute( 'size', 'L' )
			->setId( 'shirt-l' )
			->setPrice( '22.00' );

		$unitA = ( new productVariationSerialized( $parent ) )
			->setSerialNumber( 'SN-A' )
			->setPrice( '25.123456' )
			->setFieldAmount( 'points', '5.5' );

		$unitB = ( new productVariationSerialized( $parent ) )
			->setSerialNumber( 'SN-B' )
			->setPrice( '26.654321' )
			->setFieldAmount( 'points', '6.25' );

		return ( new catalog() )
			->addProduct( $parent )
			->addProduct( $variation )
			->addProduct( $unitA )
			->addProduct( $unitB );
	}
	//------------------------------------------------------------------------
	public function testRoundTrip()
	{
		$catalog = $this->buildCatalog();

		$cartOut = ( new cart( $catalog ) )
			->setStorageHandler( new cartStorageSession() );

		$cartOut->addProduct( $catalog->getProductById( 'shirt' ), 2 );
		$cartOut->addProduct( $catalog->getProductById( 'shirt-l' ), 3 );
		$cartOut->addProduct( $catalog->getProductById( 'shirt:SN-A' ), 1 );
		$cartOut->addProduct( $catalog->getProductById( 'shirt:SN-B' ), 1 );
		$cartOut->save();

		// a fresh cart over the same catalog, loaded from the session,
		// must resolve every variation and serialized unit by its id
		$cartIn = ( new cart( $catalog ) )
			->setStorageHandler( new cartStorageSession() )
			->load();

		$this->assertSame( '4', $cartIn->getDistinctItemQty() );
		$this->assertSame( '7', $cartIn->getTotalQty() );

		$this->assertSame( 2, $cartIn->getItemByProductId( 'shirt' )->getQty() );
		$this->assertSame( 3, $cartIn->getItemByProductId( 'shirt-l' )->getQty() );
		$this->assertSame( 1, $cartIn->getItemByProductId( 'shirt:SN-A' )->getQty() );
		$this->assertSame( 1, $cartIn->getItemByProductId( 'shirt:SN-B' )->getQty() );

		// the loaded items reference the same catalog product instances
		$this->assertSame(
			$catalog->getProductById( 'shirt:SN-A' ),
			$cartIn->getItemByProductId( 'shirt:SN-A' )->getProduct()
		);
		$this->assertSame(
			$catalog->getProductById( 'shirt-l' ),
			$cartIn->getItemByProductId( 'shirt-l' )->getProduct()
		);

		// totals survive the round trip in both dimensions
		// price: 2 x 20.00 + 3 x 22.00 + 25.123456 + 26.654321 = 157.777777
		$this->assertSame( '157.777777', $cartIn->getAmountTotal( $this->priceType() ) );
		$this->assertSame( $cartOut->getAmountTotal( $this->priceType() ), $cartIn->getAmountTotal( $this->priceType() ) );

		// points: 2 x 4 + 3 x 4 + 5.5 + 6.25 = 31.75
		$this->assertSame( '31.750000', $cartIn->getAmountTotal( $this->pointsType() ) );
		$this->assertSame( $cartOut->getAmountTotal( $this->pointsType() ), $cartIn->getAmountTotal( $this->pointsType() ) );
	}
	//------------------------------------------------------------------------
	public function testStoredShapeIsIdAndQtyOnly()
	{
		$catalog = $this->buildCatalog();

		$cart = ( new cart( $catalog ) )
			->setStorageHandler( new cartStorageSession() );

		$cart->addProduct( $catalog->getProductById( 'shirt:SN-A' ), 1 );
		$cart->save();

		$this->assertSame(
			[ [ 'id' => 'shirt:SN-A', 'qty' => 1 ] ],
			$_SESSION['cart_items']
		);
	}
}

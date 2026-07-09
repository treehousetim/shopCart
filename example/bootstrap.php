<?php

use treehousetim\shopCart\cart;
use treehousetim\shopCart\cartData;
use treehousetim\shopCart\catalog;
use treehousetim\shopCart\catalogTotalType;
use treehousetim\shopCart\cartStorageSession;
use treehousetim\shopCart\formatting;
use treehousetim\shopCart\product;
use treehousetim\shopCart\productAmountFormatterPrice;
use treehousetim\shopCart\totalFormatterInterface;

require __DIR__ . '/../vendor/autoload.php';

// product is abstract; a real app subclasses it per product type.
// petFoodProduct adds an emoji and a second cost dimension: stars.
class petFoodProduct extends product
{
	protected $emoji = '';
	protected $stars = '0';

	public function setEmoji( string $emoji ) : self
	{
		$this->emoji = $emoji;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getEmoji() : string
	{
		return $this->emoji;
	}
	//------------------------------------------------------------------------
	public function setStars( string $stars ) : self
	{
		$this->stars = $stars;
		return $this;
	}
	//------------------------------------------------------------------------
	public function getStars() : string
	{
		return $this->stars;
	}
	//------------------------------------------------------------------------
	public function getAmountForCatalogTotalType( catalogTotalType $type ) : string
	{
		if( $type->getType() == catalogTotalType::tPRODUCT_FIELD && $type->getProductField() == 'stars' )
		{
			return $this->stars;
		}

		return parent::getAmountForCatalogTotalType( $type );
	}
}

// formats star totals like "10 ⭐" via the library's auto-scaling unit formatter
class starsTotalFormatter implements totalFormatterInterface
{
	public function formatTotalType( string $value, catalogTotalType $type )
	{
		return formatting::unitFormatAutoScale( $value, '⭐' );
	}
}

// the user's deposited star balance, carried as cart data so
// cartStorageSession persists it in $_SESSION alongside the items.
// defined before session_start() so php can unserialize it from the session.
class starBalance extends cartData
{
	protected $type = 'starBalance';

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return [ 'type' => $this->type, 'balance' => $this->data ];
	}
}

function getStarBalance( cart $cart ) : string
{
	if( $cart->hasCartDataType( 'starBalance' ) )
	{
		return $cart->getDataByType( 'starBalance' )->getData();
	}

	return '0';
}

session_start();

// ---------------------------------------------------------------- total types
$priceTotal = ( new catalogTotalType( catalogTotalType::tPRODUCT_PRICE ) )
	->setIdentifier( 'price' )
	->setLabel( 'Order Total' );

$starsTotal = ( new catalogTotalType( catalogTotalType::tPRODUCT_FIELD ) )
	->setIdentifier( 'stars' )
	->setProductField( 'stars' )
	->setLabel( 'Star Cost' )
	->setFormatter( new starsTotalFormatter() );

// ---------------------------------------------------------------- catalog
$formatter = new productAmountFormatterPrice();

$staticCatalog = [
	[ 'id' => 'dog-food',    'name' => 'Dog Food',    'emoji' => '🐕', 'price' => '50.00',  'stars' => '10', 'desc' => 'Hearty kibble for good dogs.' ],
	[ 'id' => 'cat-food',    'name' => 'Cat Food',    'emoji' => '🐈', 'price' => '30.00',  'stars' => '2',  'desc' => 'Salmon pâté fit for royalty.' ],
	[ 'id' => 'parrot-food', 'name' => 'Parrot Food', 'emoji' => '🦜', 'price' => '25.00',  'stars' => '1',  'desc' => 'Seed and nut mix worth squawking about.' ],
	[ 'id' => 'bear-food',   'name' => 'Bear Food',   'emoji' => '🐻', 'price' => '110.00', 'stars' => '50', 'desc' => 'Honey, berries, and the occasional camper snack.' ],
];

$catalog = new catalog();

foreach( $staticCatalog as $row )
{
	$product = ( new petFoodProduct() )
		->setEmoji( $row['emoji'] )
		->setStars( $row['stars'] )
		->setId( $row['id'] )
		->setName( $row['name'] )
		->setShortDesc( $row['desc'] )
		->setPrice( $row['price'] )
		->setTotalTypeIdentifier( $priceTotal->getIdentifier() );

	$product->setFormatter( $formatter );
	$catalog->addProduct( $product );
}

// ---------------------------------------------------------------- cart
$cart = ( new cart( $catalog ) )
	->setFormatter( $formatter )
	->setStorageHandler( new cartStorageSession() )
	->load();

<?php

use treehousetim\shopCart\cart;
use treehousetim\shopCart\cartData;
use treehousetim\shopCart\catalog;
use treehousetim\shopCart\catalogTotalType;
use treehousetim\shopCart\cartStorageSession;
use treehousetim\shopCart\formatting;
use treehousetim\shopCart\product;
use treehousetim\shopCart\productAmountFormatterPrice;
use treehousetim\shopCart\productVariation;
use treehousetim\shopCart\totalFormatterInterface;

require __DIR__ . '/../vendor/autoload.php';

// product is abstract; a real app subclasses it per product type.
// petSupplyProduct adds an emoji and a second cost dimension: stars.
// category comes from the parent class (setCategory/getCategory).
class petSupplyProduct extends product
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

// the class was originally named petFoodProduct; keep the old name working
// for any code (or serialized session data) that still references it.
class_alias( petSupplyProduct::class, 'petFoodProduct' );

// a variation of a petSupplyProduct: the library handles attribute storage
// and price fallback; the storefront extras (emoji, stars) delegate to the
// parent so a variation renders exactly like its parent unless overridden
class petSupplyVariation extends productVariation
{
	public function getEmoji() : string
	{
		return $this->getParentProduct()->getEmoji();
	}
	//------------------------------------------------------------------------
	public function getStars() : string
	{
		if( $this->hasFieldAmount( 'stars' ) )
		{
			return $this->getFieldAmount( 'stars' );
		}

		return $this->getParentProduct()->getStars();
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
	// Food
	[ 'id' => 'dog-food',              'name' => 'Dog Food',              'emoji' => '🐕', 'price' => '50.00',  'stars' => '0',  'category' => 'Food',              'desc' => 'Hearty kibble for good dogs.' ],
	[ 'id' => 'cat-food',              'name' => 'Cat Food',              'emoji' => '🐈', 'price' => '30.00',  'stars' => '2',  'category' => 'Food',              'desc' => 'Salmon pâté fit for royalty.' ],
	[ 'id' => 'parrot-food',           'name' => 'Parrot Food',           'emoji' => '🦜', 'price' => '25.00',  'stars' => '1',  'category' => 'Food',              'desc' => 'Seed and nut mix worth squawking about.' ],
	[ 'id' => 'bear-food',             'name' => 'Bear Food',             'emoji' => '🐻', 'price' => '110.00', 'stars' => '50', 'category' => 'Food',              'desc' => 'Honey, berries, and the occasional camper snack.' ],

	// Toys
	[ 'id' => 'squeaky-mailman',       'name' => 'Squeaky Mailman',       'emoji' => '📬', 'price' => '12.99',  'stars' => '1',  'category' => 'Toys',              'desc' => 'Rubber mail carrier that squeaks on every triumphant chomp.' ],
	[ 'id' => 'feather-wand',          'name' => 'Feather Wand',          'emoji' => '🪶', 'price' => '8.99',   'stars' => '1',  'category' => 'Toys',              'desc' => 'Feathers on a stick. Cat cardio, human arm day.' ],
	[ 'id' => 'parrot-puzzle-box',     'name' => 'Parrot Puzzle Box',     'emoji' => '🧩', 'price' => '19.99',  'stars' => '3',  'category' => 'Toys',              'desc' => 'Foraging puzzle for beaks that already outsmart your locks.' ],
	[ 'id' => 'log-to-maul',           'name' => 'Log to Maul',           'emoji' => '🪵', 'price' => '34.99',  'stars' => '5',  'category' => 'Toys',              'desc' => 'Genuine forest log for bears. Pre-scratched for authenticity.' ],

	// Beds
	[ 'id' => 'orthopedic-dog-bed',    'name' => 'Orthopedic Dog Bed',    'emoji' => '🛏️', 'price' => '89.00',  'stars' => '4',  'category' => 'Beds',              'desc' => 'Memory foam support for senior nappers and dramatic flops.' ],
	[ 'id' => 'cat-sun-hammock',       'name' => 'Cat Sun Hammock',       'emoji' => '☀️', 'price' => '42.50',  'stars' => '3',  'category' => 'Beds',              'desc' => 'Window-mounted hammock that follows the sunbeam economy.' ],
	[ 'id' => 'parrot-snuggle-hut',    'name' => 'Parrot Snuggle Hut',    'emoji' => '🏕️', 'price' => '27.99',  'stars' => '2',  'category' => 'Beds',              'desc' => 'A cozy fleece tent for tired wings and big opinions.' ],
	[ 'id' => 'bear-cave-mattress',    'name' => 'Bear Cave Mattress',    'emoji' => '⛰️', 'price' => '240.00', 'stars' => '60', 'category' => 'Beds',              'desc' => 'King-size and hibernation-rated for a full five months.' ],

	// Leashes & Collars
	[ 'id' => 'reflective-leash',      'name' => 'Reflective Leash',      'emoji' => '🦮', 'price' => '21.99',  'stars' => '0',  'category' => 'Leashes & Collars', 'desc' => 'Six feet of night-walk visibility with a padded handle.' ],
	[ 'id' => 'bell-collar',           'name' => 'Bell Collar',           'emoji' => '🔔', 'price' => '9.99',   'stars' => '1',  'category' => 'Leashes & Collars', 'desc' => 'Breakaway collar that gives the songbirds a fighting chance.' ],
	[ 'id' => 'hiking-bear-bell',      'name' => 'Hiking Bear Bell',      'emoji' => '🛎️', 'price' => '14.99',  'stars' => '2',  'category' => 'Leashes & Collars', 'desc' => 'Lets campers know your bear is coming. Technically a dinner bell.' ],

	// Bowls & Feeders
	[ 'id' => 'slow-feeder-bowl',      'name' => 'Slow Feeder Bowl',      'emoji' => '🥣', 'price' => '16.99',  'stars' => '0',  'category' => 'Bowls & Feeders',   'desc' => 'Maze-bottom bowl that turns inhaling dinner into a hobby.' ],
	[ 'id' => 'cat-water-fountain',    'name' => 'Cat Water Fountain',    'emoji' => '⛲', 'price' => '39.99',  'stars' => '2',  'category' => 'Bowls & Feeders',   'desc' => 'Filtered, whisper-quiet, and still less popular than the faucet.' ],
	[ 'id' => 'parrot-seed-dispenser', 'name' => 'Parrot Seed Dispenser', 'emoji' => '🌻', 'price' => '24.99',  'stars' => '1',  'category' => 'Bowls & Feeders',   'desc' => 'Anti-scatter dispenser that keeps the sunflower rain indoors.' ],
	[ 'id' => 'honey-trough',          'name' => 'Honey Trough',          'emoji' => '🍯', 'price' => '64.99',  'stars' => '12', 'category' => 'Bowls & Feeders',   'desc' => 'Cast-iron trough sized for bear-scale honey enthusiasm.' ],

	// Grooming
	[ 'id' => 'deshedding-brush',      'name' => 'De-shedding Brush',     'emoji' => '🪮', 'price' => '18.99',  'stars' => '1',  'category' => 'Grooming',          'desc' => 'Reclaims enough dog fur each week to knit a second dog.' ],
	[ 'id' => 'claw-clippers',         'name' => 'Claw Clippers',         'emoji' => '✂️', 'price' => '11.99',  'stars' => '0',  'category' => 'Grooming',          'desc' => 'Precision cat claw clippers. Bribery treats sold separately.' ],
	[ 'id' => 'beak-conditioner',      'name' => 'Beak Conditioner',      'emoji' => '💅', 'price' => '13.49',  'stars' => '1',  'category' => 'Grooming',          'desc' => 'Cuttlebone polish for a show-ring shine and a sharper hello.' ],
	[ 'id' => 'bear-fur-rake',         'name' => 'Bear Fur Rake',         'emoji' => '🧹', 'price' => '29.99',  'stars' => '4',  'category' => 'Grooming',          'desc' => 'Garden-grade rake for industrial-grade spring shedding.' ],

	// Habitats
	[ 'id' => 'parrot-palace',         'name' => 'Parrot Palace',         'emoji' => '🏰', 'price' => '199.00', 'stars' => '20', 'category' => 'Habitats',          'desc' => 'Three perches, a swing, and room for a very loud monarch.' ],
	[ 'id' => 'cat-tower',             'name' => 'Cat Tower',             'emoji' => '🗼', 'price' => '129.00', 'stars' => '8',  'category' => 'Habitats',          'desc' => 'Five levels of vertical territory and one scratchable throne.' ],
];

$catalog = new catalog();

foreach( $staticCatalog as $row )
{
	$product = ( new petSupplyProduct() )
		->setEmoji( $row['emoji'] )
		->setStars( $row['stars'] )
		->setId( $row['id'] )
		->setName( $row['name'] )
		->setCategory( $row['category'] )
		->setShortDesc( $row['desc'] )
		->setPrice( $row['price'] )
		->setTotalTypeIdentifier( $priceTotal->getIdentifier() );

	$product->setFormatter( $formatter );
	$catalog->addProduct( $product );
}

// ---------------------------------------------------------------- variations
// the blanket comes in four sizes. each size is its own catalog entry so the
// cart and session storage resolve it by id; price falls back to the parent
// unless overridden — L and XL cost 1.5x the base price.
$blanket = ( new petSupplyProduct() )
	->setEmoji( '🧶' )
	->setStars( '2' )
	->setId( 'pet-blanket' )
	->setName( 'Pet Blanket' )
	->setCategory( 'Beds' )
	->setShortDesc( 'Machine-washable fleece for dens, crates, and couch forts.' )
	->setPrice( '24.00' )
	->setTotalTypeIdentifier( $priceTotal->getIdentifier() );

$blanket->setFormatter( $formatter );

// the parent itself is not added to the catalog — only its sizes are buyable
foreach( [ 'S', 'M', 'L', 'XL' ] as $size )
{
	$variation = new petSupplyVariation( $blanket );
	$variation
		->setAttribute( 'size', $size )
		->setId( 'pet-blanket-' . strtolower( $size ) )
		->setName( $blanket->getName() . ' (' . $size . ')' );

	if( in_array( $size, [ 'L', 'XL' ], true ) )
	{
		$variation->setPrice( bcmul( $blanket->getPrice(), '1.5', 2 ) );
	}

	$catalog->addProduct( $variation );
}

// ---------------------------------------------------------------- cart
$cart = ( new cart( $catalog ) )
	->setFormatter( $formatter )
	->setStorageHandler( new cartStorageSession() )
	->load();

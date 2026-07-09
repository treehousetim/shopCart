# shopCart
Shopping Cart Core Functionality PHP Library

## Installing

`composer require treehousetim/shopcart`

## Interfaces and Abstract Classes

In order to use the shopCart, you will need to instantiate both a `treehousetim\shopCart\cart` and a `treehousetim\shopCart\catalog` object.

The constructor for the cart requires a catalog object be passed.
In order for a catalog object to function, it requires a product loader be implemented that implements `catalogLoaderInterface`


```php
use \treehousetim\shopCart\catalog;
use \treehousetim\shopCart\cart;

$catalog = ( new catalog() )
	->setProductLoader( new \application\cart\myProductLoader() )
	->populate();

$cart = new cart( $catalog );
$cart->setTotalTypeLoader( (new \application\cart\myCatalogTotalTypeLoader()))
	->setFormatter( new \application\cart\myProductAmountFormatter() )
	->setStorageHandler( (new \treehousetim\shopCart\cartStorageSession() ) )
	->load();

```

## catalogLoaderInterface
A catalog object is used to load products into the product catalog.  

```php
interface catalogLoaderInterface
{
	public function hasProducts() : bool;
	public function nextProduct() : bool;
	public function getProduct() : product;
}
```

## cartStorageInterface

You can use a lot of different storage options for a cart.  `treehousetim\shopCart` provides an implementation for session storage.

After creating your implementation of this interface, you must supply it to your cart using `$cart->setStorageHandler( $storageHandler );`

```php
interface cartStorageInterface
{
	public function loadCart( cart $cart ) : cartStorageInterface;
	public function emptyCart( cart $cart ) : cartStorageInterface;

	public function saveItems( array $items ) : cartStorageInterface;
	public function saveData( array $data ) : cartStorageInterface;

	// used to clean up after all storage is complete
	public function finalize( cart $cart ) : cartStorageInterface;
}
```

## catalogTotalTypeLoaderInterface

You must implement a class that conforms to this interface to support different types of product totals. Typical e-commerce shop carts will probably only need a single catalogTotalType for price, but there are other businesses that need totals of different product properties.

```php
interface catalogTotalTypeLoaderInterface
{
	public function nextType() : bool;
	public function getType() : catalogTotalType;
	public function resetType();
}
```

Here is a sample catalogTotalTypeLoaderInterface implementation for both price and some special case of "points"

```php
<?php namespace application\libraries\cart;

use treehousetim\shopCart\catalogTotalTypeLoaderInterface;
use treehousetim\shopCart\catalogTotalType;

class totalTypeLoader implements catalogTotalTypeLoaderInterface
{
	protected $types;

	public function __construct()
	{
		$this->types[] = (new catalogTotalType( catalogTotalType::tPRODUCT_PRICE ) );
		$this->types[] = (new catalogTotalType( catalogTotalType::tPRODUCT_FIELD ) )
			->setIdentifier( 1 )
			->setUnit( 'points' )
			->setLabel( 'Point' )
			->setProductField( 'productPoints' );
	}
	//------------------------------------------------------------------------
	public function nextType() : bool
	{
		next( $this->types );
		if( key( $this->types ) === null )
		{
			return false;
		}

		return true;
	}
	//------------------------------------------------------------------------
	public function getType() : catalogTotalType
	{
		return current( $this->types );
	}
	//------------------------------------------------------------------------
	public function resetType()
	{
		reset( $this->types );
	}
}
```

## Product Variations

A product can have variations along arbitrary attributes (color, size, …) using
`productVariation`. A variation wraps a parent `product` and falls back to the
parent for anything you don't override — name, description, image, category,
formatter, total type identifier, and price.

Every variation is itself a `product`: give it a unique id and add it to the
catalog alongside its parent. Because cart items are keyed by product id and
`cartStorageSession` re-resolves products from the catalog on load, this is all
that's needed for variations to survive a save/load round trip.

```php
use treehousetim\shopCart\productVariation;

$shirt = ( new myProduct() )
	->setId( 'shirt' )
	->setName( 'T-Shirt' )
	->setPrice( '20.00' );

$shirtXL = ( new productVariation( $shirt ) )
	->setAttribute( 'size', 'XL' )
	->setAttribute( 'color', 'blue' )
	->setId( 'shirt-xl-blue' )
	->setPrice( '24.50' );     // optional — omit to inherit the parent's price

$catalog->addProduct( $shirt )->addProduct( $shirtXL );

$shirtXL->getAttribute( 'size' );  // 'XL' (unknown names throw
                                   // Exception::noSuchAttributeErrorCode)
$shirtXL->getAttributes();         // ['size' => 'XL', 'color' => 'blue']
```

For carts with multiple total types, a variation can also override the per-unit
amount of any `catalogTotalType::tPRODUCT_FIELD` dimension with
`setFieldAmount( $productField, $amount )`. When no field amount is set for a
dimension, `getAmountForCatalogTotalType()` delegates to the parent product.

## Serialized Variations

`productVariationSerialized` models variations where each unit is a distinct
physical item identified by a serial number. Rules enforced by the library:

- **Quantity is always exactly 1 per serial.** `cart::addProduct()` with a
  quantity other than 1, or `cart::updateItemQty()` to anything other than 1,
  throws `Exception::serializedQtyErrorCode`.
- **Adding the same serial twice throws** `Exception::duplicateSerialErrorCode`
  (an exception rather than a silent no-op, so the UI can tell the shopper the
  unit is already in the cart). The cart is left unchanged.
- **Id resolution:** if you don't call `setId()`, the id is derived as
  `parentId . ':' . serialNumber`, so each unit is unique in the catalog and
  the session round trip works unchanged. Add every serialized unit to the
  catalog like any other product.

Each serialized unit can carry its own amount for *each* total type — its own
price and its own per-unit amount for any `tPRODUCT_FIELD` dimension — so two
serials of the same product can total differently in every dimension:

```php
use treehousetim\shopCart\productVariationSerialized;

$unitA = ( new productVariationSerialized( $collectible ) )
	->setSerialNumber( 'SN-0001' )
	->setPrice( '102.75' )
	->setFieldAmount( 'points', '7.5' );

$unitB = ( new productVariationSerialized( $collectible ) )
	->setSerialNumber( 'SN-0002' )
	->setPrice( '99.10' )
	->setFieldAmount( 'points', '3.25' );

$catalog->addProduct( $unitA )->addProduct( $unitB );

$cart->addProduct( $unitA, 1 );    // qty must be exactly 1
$cart->addProduct( $unitB, 1 );
// cart::getAmountTotal()/getTotal() now sum the per-serial amounts
```

`product::isSerialized()` (false on every product except serialized variations)
lets storage handlers and UIs detect these items generically.

## totalFormatterInterface

```php
interface totalFormatterInterface
{
	public function formatTotalType( string $value, catalogTotalType $type );
}
```


## Testing
If you have cloned this repo, you can run the tests.
There are no dependencies, but PHPUnit is installed with composer.

1. `composer install`
2. `./vendor/bin/phpunit`

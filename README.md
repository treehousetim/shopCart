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

## totalFormatterInterface

```php
interface totalFormatterInterface
{
	public function formatTotalType( string $value, catalogTotalType $type );
}
```


## Testing
If you have cloned this repo, you can run the tests (there are none yet).
There are no dependencies, but PHPUnit is installed with composer.

1. `composer install`
2. `./vendor/bin/phpunit --bootstrap vendor/autoload.php test`

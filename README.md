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
A catalog object must load products into the product catalog.  The interface for this is:

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

After creating your implementation of this interface, you would supply it to your cart using `$cart->setStorageHandler( $storageHandler );`

```php
interface cartStorageInterface
{
	public function init( cart $cart ) : cartStorageInterface;
	public function saveCartItem( cartItem $item ) : cartStorageInterface;
	public function saveCartData( cartData $data ) : cartStorageInterface;
	public function loadCart( cart $cart ) : cartStorageInterface;
}
```

## catalogTotalTypeLoaderInterface

```php
interface catalogTotalTypeLoaderInterface
{
	public function nextType() : bool;
	public function getType() : catalogTotalType;
	public function resetType();
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

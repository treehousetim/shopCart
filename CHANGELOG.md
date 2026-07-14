# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added

- **Product variations** — new `productVariation` class (extends `product`).
  A variation wraps a parent product, carries arbitrary attribute name/value
  pairs (`setAttribute()`/`getAttribute()`/`hasAttribute()`/`getAttributes()`),
  and falls back to the parent for any unset value: name, short description,
  image, category, formatter, total type identifier, and price. A price set on
  the variation overrides the parent's. `setFieldAmount( $field, $amount )`
  overrides the per-unit amount of any `catalogTotalType::tPRODUCT_FIELD`
  dimension; unset dimensions delegate to the parent's
  `getAmountForCatalogTotalType()`. Variations are ordinary catalog products —
  give each a unique id and `catalog::addProduct()` it — so the
  `cartStorageSession` id/qty round trip works unchanged.
- **Serialized variations** — new `productVariationSerialized` class (extends
  `productVariation`) for variations where every unit is a distinct physical
  item identified by a serial number. Quantity per serial is always exactly 1:
  adding the same serial to a cart twice throws
  `Exception::duplicateSerialErrorCode` (the cart is left unchanged), and any
  attempt to set a serialized item's quantity to anything other than 1
  (`cart::addProduct()` qty, `cart::updateItemQty()`,
  `cartItem::setQty()`/`updateQty()`/`addQty()`) throws
  `Exception::serializedQtyErrorCode`. When no id is set explicitly, the id is
  derived as `parentId . ':' . serialNumber`. Each unit can carry its own
  price and its own `setFieldAmount()` per `tPRODUCT_FIELD` dimension, so two
  serials of the same product can total differently in every dimension.
- `product::isSerialized()` — returns `false` on every product except
  serialized variations; lets carts, storage handlers, and UIs treat
  serialized items generically.
- New `Exception` error codes: `noSuchAttributeErrorCode` (6),
  `duplicateSerialErrorCode` (7), `serializedQtyErrorCode` (8),
  `noSuchFieldAmountErrorCode` (9).
- Full [Pest](https://pestphp.com/) 3 unit-test suite under `test/` covering
  every class — formatting, formatters, `cart`/`cartItem` totals, `catalog`,
  `product`, `catalogTotalType`, `cartData`, `Exception`, and the product
  variations / serialized units / session storage round trip. Run with
  `./vendor/bin/pest`. Pest is `require-dev` only, so it does not affect the
  library's `^7.0` runtime support — but note the test runner itself requires
  PHP 8.2+, so the suite is no longer executed on PHP 7.
- **PHP 8 support** — the `php` constraint widens from `^7.0` to
  `^7.0 || ^8.0`, and the dev PHPUnit constraint from `^6` to `^6 || ^9.3` so
  the suite runs on PHP 8.

### Fixed

- **Totals silently truncated to whole numbers.** Every `bcmul()`/`bcadd()`
  call (`cartItem::getTotalAmount()`, `cartItem::getTotalTypeAmount()`,
  `cart::getTotal()`, `cart::getAmountTotal()`,
  `productAmountFormatter::formatCartItemPrice()`) omitted the scale argument.
  bcmath's default scale is 0, so unless the host application happened to call
  `bcscale()` globally, 3 × $19.99 totaled $59, not $59.97. All calls now pass
  `formatting::$longScale` (6) explicitly.
- **PHP 8 compatibility** — `cart::setTotalTypeLoader()` assigned the loader to an
  undeclared `$typeLoader` property instead of the declared `$totalTypeLoader`.
  On PHP 8.2+ this created a deprecated dynamic property (deprecation warning on
  every cart setup). All internal uses now reference `$totalTypeLoader`.
- `cartData` declared `implements jsonSerializable`, which resolved to a
  nonexistent interface inside the library namespace and was a fatal error the
  moment the class loaded. It now implements `\JsonSerializable`, and
  `jsonSerialize()` carries `#[\ReturnTypeWillChange]` to silence the PHP 8.1
  tentative-return-type deprecation (the attribute parses as a comment on PHP 7,
  so PHP `^7.0` support is unchanged).
- `cartStorageSession::saveItems()` / `saveData()` were missing the
  `: cartStorageInterface` return types required by `cartStorageInterface`,
  making the class impossible to load on any PHP version.
- `cartStorageSession::saveData()` called `getForStorage()` on the whole data
  array instead of each item (fatal whenever the cart had data to save).
- `cartPriceTotalFormatter::formatTotalType()` read the protected
  `catalogTotalType::$type` property directly (fatal); it now uses `getType()`.
- `formatting::longNumberFormat()` referenced `self::longScale` as a constant;
  it is the static property `self::$longScale`.
- `formatting::unitFormat()` called an undefined bare `longNumberFormat()`
  function; it now calls `self::longNumberFormat()`.
- `formatting::unitFormatAutoScale()` corrupted displayed numbers:
  `trim( $number, ' -0' )` stripped significant zeros (`100` displayed as `1`,
  `0.5` as `.5`). Trailing zeros are now trimmed only after a decimal point and
  leading/significant zeros are preserved. It also emitted a malformed closing
  tag (`</unit` instead of `</span>`).
- `productAmountFormatterPrice` referenced the nonexistent constant
  `productAmountFormatter::tPrice` (constants are case-sensitive; the constant
  is `tPRICE`).
- `catalogTotalType::setFormatter()` type-hinted the nonexistent
  `shopCartTotalFormatterInterface`; it now accepts `totalFormatterInterface`.
- README: the sample `catalogTotalTypeLoaderInterface` implementation had an
  extra closing brace in its constructor.

### Changed

- **No third-party dependencies** — the `treehousetim/exception` package
  requirement was dropped from `composer.json`. Its ~15-line base class (a
  variadic message/code constructor over `\LogicException`) is now inlined into
  `treehousetim\shopCart\Exception`, so behavior is unchanged: `Exception` still
  extends `\LogicException`, still accepts `new Exception( 'message', $code )`,
  and all error-code constants are unchanged. The only remaining `require` entry
  besides `php` is the `ext-bcmath` platform extension.
- **Requirement now declared** — `composer.json` adds `ext-bcmath: *`. The
  library has always called `bcadd()`/`bcmul()` at runtime, so this only makes
  an existing implicit dependency explicit, but `composer install`/`update`
  will now refuse to resolve on a PHP build compiled without the bcmath
  extension where it previously installed (and then fataled on first total).
- **BREAKING** — `cartStorageSession` now stores the `iCartData` objects
  themselves in `$_SESSION['cart_data']` instead of their `getForStorage()`
  output. The previous behavior was unusable: `loadCart()` expected objects and
  fatally called `setType()` on the stored arrays, so any save/load round trip
  with cart data crashed. Carts saved to sessions by earlier versions should be
  discarded (or the session cleared) when upgrading.
- **BREAKING** — `catalogTotalType::format()` signature changed from
  `format()` to `format( string $value )` and it now returns the formatted
  value. The old form called `totalFormatterInterface::formatTotalType()` with
  one argument instead of two and could never have worked.
- **BREAKING** — `productAmountFormatterPrice` was rewritten. It previously
  declared a single `format( product $product )` method, left its parent's
  three abstract methods unimplemented (so the class could not be
  instantiated), and read an undeclared `$this->type`. It now implements
  `formatProduct()`, `formatCartItem()`, and `formatCartTotal()` for the
  `price` type and throws `Exception::invalidFormatErrorCode` for any other
  type. The old `format()` method is gone.

### Removed

- **BREAKING** — `cart::getAmountOrdered()`. It overwrote its accumulator on
  each loop iteration and returned only the last item's amount; a corrected
  version would be identical to `cart::getAmountTotal()`, which remains — use
  that instead.
- **BREAKING** — `cart::populate()`. It instantiated a `productModel` class
  that does not exist in the library (fatal if called). Use a
  `catalogLoaderInterface` implementation with `catalog::populate()` and
  `cart::load()` instead.
- **BREAKING** — `cartItem::formatTotalType()`. It delegated to
  `product::formatTotalType()`, which does not exist (fatal if called).
- **BREAKING** — unused `cart` properties `$productsByMetal` (public),
  `$totals`, and `$metalsCollection`. Nothing in the library read or wrote
  them.
- `src/functions.php` — a file of global, application-specific functions
  (metals/premium domain) that was never registered with the composer
  autoloader and duplicated the `formatting` class.
- `src/shopCart.php` — contained `cartMetalCollection`, an
  application-specific class that could never be autoloaded (the PSR-4 class
  name does not match the file name).

*Although several removed and changed methods are marked BREAKING, every one of
them was already broken — each either fataled when called or returned wrong
results — so no working integration can depend on the old behavior.*

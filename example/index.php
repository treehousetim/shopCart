<?php

use treehousetim\shopCart\productAmountFormatterPrice;

require __DIR__ . '/bootstrap.php';

// ---------------------------------------------------------------- actions (post/redirect/get)
if( $_SERVER['REQUEST_METHOD'] === 'POST' )
{
	$id = $_POST['id'] ?? '';

	switch( $_POST['action'] ?? '' )
	{
	case 'add':
		$cart->addProduct( $catalog->getProductById( $id ), max( 1, (int)( $_POST['qty'] ?? 1 ) ) );
		break;

	case 'update':
		$qty = (int)( $_POST['qty'] ?? 0 );
		if( $qty < 1 )
		{
			$cart->removeItem( $cart->getItemByProductId( $id ) );
		}
		else
		{
			$cart->updateItemQty( $id, $qty );
		}
		break;

	case 'remove':
		$cart->removeItem( $cart->getItemByProductId( $id ) );
		break;

	case 'empty':
		// emptyCart() clears cart data too; the star balance survives it
		$balance = getStarBalance( $cart );
		$cart->emptyCart();
		$cart->addData( ( new starBalance() )->setData( $balance ) );
		break;

	case 'deposit':
		$balance = bcadd( getStarBalance( $cart ), max( 1, (int)( $_POST['stars'] ?? 10 ) ), 0 );
		$cart->addData( ( new starBalance() )->setData( $balance ) );
		break;
	}

	$cart->save();

	// forms post back to the current url, so redirecting to REQUEST_URI
	// preserves any active category filter and search
	header( 'Location: ' . $_SERVER['REQUEST_URI'] );
	exit;
}

// ---------------------------------------------------------------- catalog filtering (GET)
$filterCategory = trim( $_GET['category'] ?? '' );
$search = trim( $_GET['q'] ?? '' );

// unique categories in catalog order
$categories = [];

foreach( $catalog->getProducts() as $product )
{
	if( ! in_array( $product->getCategory(), $categories, true ) )
	{
		$categories[] = $product->getCategory();
	}
}

// an unknown ?category= value just means nothing matches "exactly"; treat it as All
if( $filterCategory !== '' && ! in_array( $filterCategory, $categories, true ) )
{
	$filterCategory = '';
}

function productMatches( petSupplyProduct $product, string $category, string $search ) : bool
{
	if( $category !== '' && $product->getCategory() !== $category )
	{
		return false;
	}

	if( $search !== '' && stripos( $product->getName() . ' ' . $product->getShortDesc(), $search ) === false )
	{
		return false;
	}

	return true;
}
//------------------------------------------------------------------------
function filterUrl( string $category, string $search ) : string
{
	$params = array_filter( [ 'category' => $category, 'q' => $search ] );
	return 'index.php' . ( $params ? '?' . http_build_query( $params ) : '' );
}

$visibleProducts = [];

foreach( $catalog->getProducts() as $product )
{
	if( productMatches( $product, $filterCategory, $search ) )
	{
		$visibleProducts[] = $product;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Critter Supply Co. — shopCart example</title>
<style>
	body { font-family: system-ui, sans-serif; margin: 0 auto; max-width: 60rem; padding: 1rem; color: #222; }
	header { display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: .5rem; border-bottom: 2px solid #222; margin-bottom: 1.5rem; padding-bottom: .5rem; }
	header h1 { margin: 0; }
	.filters { display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; margin-bottom: 1rem; }
	.filters a { padding: .3rem .8rem; border: 1px solid #ccc; border-radius: 1rem; text-decoration: none; color: #222; font-size: .9rem; }
	.filters a:hover { border-color: #222; }
	.filters a.active { background: #222; border-color: #222; color: #fff; }
	.search { display: flex; gap: .4rem; margin-left: auto; }
	.search input[type=search] { padding: .3rem .6rem; border: 1px solid #ccc; border-radius: 1rem; }
	.result-note { color: #555; font-size: .9rem; margin: 0 0 1rem; }
	.products { display: grid; grid-template-columns: repeat(auto-fill, minmax(13rem, 1fr)); gap: 1rem; }
	.card { border: 1px solid #ccc; border-radius: .5rem; padding: 1rem; text-align: center; display: flex; flex-direction: column; }
	.card:hover { border-color: #999; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
	.card .emoji { font-size: 3rem; }
	.card .category { display: inline-block; margin: 0 auto; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: #555; background: #f0f0f0; border-radius: 1rem; padding: .15rem .6rem; }
	.card h4 { margin: .5rem 0 .25rem; }
	.card p { min-height: 3em; font-size: .85rem; color: #555; flex-grow: 1; }
	.card .cost { margin-bottom: .5rem; }
	table { width: 100%; border-collapse: collapse; margin-top: .5rem; }
	th, td { text-align: left; padding: .4rem .6rem; border-bottom: 1px solid #ddd; }
	td.num, th.num { text-align: right; }
	tfoot td { font-weight: bold; border-top: 2px solid #222; }
	input[type=number] { width: 4rem; }
	button { cursor: pointer; }
	.checkout-link { display: inline-block; margin-top: 1rem; padding: .5rem 1.5rem; background: #222; color: #fff; border-radius: .3rem; text-decoration: none; }
</style>
</head>
<body>

<header>
	<h1>🐾 Critter Supply Co.</h1>
	<div>
		⭐ balance: <?= getStarBalance( $cart ) ?>
		<form method="post" style="display: inline">
			<input type="hidden" name="action" value="deposit">
			<input type="number" name="stars" value="10" min="1">
			<button type="submit">Deposit stars</button>
		</form>
		&nbsp; 🛒 <?= $cart->getTotalQty() ?> item(s) in cart
	</div>
</header>

<h2>Catalog</h2>

<nav class="filters">
	<a href="<?= htmlspecialchars( filterUrl( '', $search ) ) ?>" class="<?= $filterCategory === '' ? 'active' : '' ?>">All</a>
	<?php foreach( $categories as $category ): ?>
	<a href="<?= htmlspecialchars( filterUrl( $category, $search ) ) ?>" class="<?= $filterCategory === $category ? 'active' : '' ?>"><?= htmlspecialchars( $category ) ?></a>
	<?php endforeach ?>
	<form class="search" method="get" action="index.php">
		<?php if( $filterCategory !== '' ): ?>
		<input type="hidden" name="category" value="<?= htmlspecialchars( $filterCategory ) ?>">
		<?php endif ?>
		<input type="search" name="q" value="<?= htmlspecialchars( $search ) ?>" placeholder="Search products…">
		<button type="submit">Search</button>
	</form>
</nav>

<?php if( $filterCategory !== '' || $search !== '' ): ?>
<p class="result-note">
	Showing <?= count( $visibleProducts ) ?> of <?= count( $catalog->getProducts() ) ?> products<?= $filterCategory !== '' ? ' in ' . htmlspecialchars( $filterCategory ) : '' ?><?= $search !== '' ? ' matching “' . htmlspecialchars( $search ) . '”' : '' ?>
	— <a href="index.php">clear filters</a>
</p>
<?php endif ?>

<?php if( ! $visibleProducts ): ?>
<p>No products match. Even the bear couldn't find anything.</p>
<?php else: ?>
<div class="products">
	<?php foreach( $visibleProducts as $product ): ?>
	<div class="card">
		<div class="emoji"><?= $product->getEmoji() ?></div>
		<span class="category"><?= htmlspecialchars( $product->getCategory() ) ?></span>
		<?= $product->getNameHeader() ?>
		<p><?= htmlspecialchars( $product->getShortDesc() ) ?></p>
		<div class="cost">
			<?= $product->formatAmount( productAmountFormatterPrice::tPRICE ) ?>
			+ <?= $product->getStars() ?> ⭐
		</div>
		<form method="post">
			<input type="hidden" name="action" value="add">
			<input type="hidden" name="id" value="<?= htmlspecialchars( $product->getId() ) ?>">
			<input type="number" name="qty" value="1" min="1">
			<button type="submit">Add to cart</button>
		</form>
	</div>
	<?php endforeach ?>
</div>
<?php endif ?>

<h2>Your Cart</h2>
<?php if( ! $cart->getCartItems() ): ?>
	<p>Your cart is empty. Your pets are judging you.</p>
<?php else: ?>
<table>
	<thead>
		<tr><th>Product</th><th class="num">Qty</th><th class="num">Line Total</th><th class="num">Stars</th><th></th></tr>
	</thead>
	<tbody>
		<?php foreach( $cart->getCartItems() as $item ): ?>
		<tr>
			<td><?= $item->getProduct()->getEmoji() ?> <?= htmlspecialchars( $item->getProduct()->getName() ) ?></td>
			<td class="num">
				<form method="post">
					<input type="hidden" name="action" value="update">
					<input type="hidden" name="id" value="<?= htmlspecialchars( $item->getProduct()->getId() ) ?>">
					<input type="number" name="qty" value="<?= $item->getQty() ?>" min="0">
					<button type="submit">Update</button>
				</form>
			</td>
			<td class="num"><?= $item->formatAmount( productAmountFormatterPrice::tPRICE ) ?></td>
			<td class="num"><?= $starsTotal->format( $item->getTotalAmount( $starsTotal ) ) ?></td>
			<td>
				<form method="post">
					<input type="hidden" name="action" value="remove">
					<button type="submit">Remove</button>
					<input type="hidden" name="id" value="<?= htmlspecialchars( $item->getProduct()->getId() ) ?>">
				</form>
			</td>
		</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot>
		<tr>
			<td><?= htmlspecialchars( $priceTotal->getLabel() ) ?> / <?= htmlspecialchars( $starsTotal->getLabel() ) ?></td>
			<td class="num"><?= $cart->getTotalQty() ?></td>
			<td class="num"><?= $cart->getTotalFormatted( $priceTotal ) ?></td>
			<td class="num"><?= $starsTotal->format( $cart->getAmountTotal( $starsTotal ) ) ?></td>
			<td>
				<form method="post">
					<input type="hidden" name="action" value="empty">
					<button type="submit">Empty cart</button>
				</form>
			</td>
		</tr>
	</tfoot>
</table>

<?php
	$starCost = $cart->getAmountTotal( $starsTotal );
	$shortfall = bcsub( $starCost, getStarBalance( $cart ), 0 );
?>
<?php if( bccomp( $shortfall, '0' ) > 0 ): ?>
	<p>You need <?= $shortfall ?> more ⭐ to cover this order — use the deposit button above.</p>
<?php else: ?>
	<p>✅ Your star balance covers this order.</p>
<?php endif ?>

<a class="checkout-link" href="checkout.php">Proceed to checkout →</a>
<?php endif ?>

</body>
</html>

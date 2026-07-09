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

	header( 'Location: ' . $_SERVER['PHP_SELF'] );
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Critter Chow — shopCart example</title>
<style>
	body { font-family: system-ui, sans-serif; margin: 0 auto; max-width: 60rem; padding: 1rem; color: #222; }
	header { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 2px solid #222; margin-bottom: 1.5rem; }
	.products { display: grid; grid-template-columns: repeat(auto-fill, minmax(13rem, 1fr)); gap: 1rem; }
	.card { border: 1px solid #ccc; border-radius: .5rem; padding: 1rem; text-align: center; }
	.card .emoji { font-size: 3rem; }
	.card h4 { margin: .5rem 0 .25rem; }
	.card p { min-height: 3em; font-size: .85rem; color: #555; }
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
	<h1>🥣 Critter Chow</h1>
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
<div class="products">
	<?php foreach( $catalog->getProducts() as $product ): ?>
	<div class="card">
		<div class="emoji"><?= $product->getEmoji() ?></div>
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

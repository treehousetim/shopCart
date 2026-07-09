<?php

use treehousetim\shopCart\productAmountFormatterPrice;

require __DIR__ . '/bootstrap.php';

$starCost = $cart->getAmountTotal( $starsTotal );
$balance = getStarBalance( $cart );
$shortfall = bcsub( $starCost, $balance, 0 );
$errors = [];

// ---------------------------------------------------------------- place order (post/redirect/get)
if( $_SERVER['REQUEST_METHOD'] === 'POST' )
{
	$name = trim( $_POST['name'] ?? '' );
	$email = trim( $_POST['email'] ?? '' );

	if( ! $cart->getCartItems() )
	{
		$errors[] = 'Your cart is empty.';
	}

	if( $name === '' )
	{
		$errors[] = 'Name is required.';
	}

	if( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) )
	{
		$errors[] = 'A valid email is required.';
	}

	if( bccomp( $shortfall, '0' ) > 0 )
	{
		$errors[] = 'You need ' . $shortfall . ' more ⭐ to place this order.';
	}

	if( ! $errors )
	{
		$lines = [];
		foreach( $cart->getCartItems() as $item )
		{
			$lines[] = [
				'name' => $item->getProduct()->getEmoji() . ' ' . $item->getProduct()->getName(),
				'qty' => $item->getQty(),
				'lineTotal' => $item->formatAmount( productAmountFormatterPrice::tPRICE ),
				'stars' => $starsTotal->format( $item->getTotalAmount( $starsTotal ) ),
			];
		}

		// the "order" lives in the session only — this example has no backend
		$_SESSION['last_order'] = [
			'number' => 'CC-' . strtoupper( bin2hex( random_bytes( 4 ) ) ),
			'name' => $name,
			'email' => $email,
			'lines' => $lines,
			'total' => $cart->getTotalFormatted( $priceTotal ),
			'starCost' => $starsTotal->format( $starCost ),
		];

		// pay the stars, then empty the cart (emptyCart() clears cart data,
		// so the remaining balance is re-added afterward)
		$newBalance = bcsub( $balance, $starCost, 0 );
		$cart->emptyCart();
		$cart->addData( ( new starBalance() )->setData( $newBalance ) );
		$cart->save();

		header( 'Location: checkout.php?placed=1' );
		exit;
	}
}

$placedOrder = null;

if( isset( $_GET['placed'] ) && isset( $_SESSION['last_order'] ) )
{
	$placedOrder = $_SESSION['last_order'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Checkout — Critter Chow</title>
<style>
	body { font-family: system-ui, sans-serif; margin: 0 auto; max-width: 40rem; padding: 1rem; color: #222; }
	header { display: flex; justify-content: space-between; align-items: baseline; border-bottom: 2px solid #222; margin-bottom: 1.5rem; }
	table { width: 100%; border-collapse: collapse; margin: .5rem 0 1.5rem; }
	th, td { text-align: left; padding: .4rem .6rem; border-bottom: 1px solid #ddd; }
	td.num, th.num { text-align: right; }
	tfoot td { font-weight: bold; border-top: 2px solid #222; }
	label { display: block; margin-top: .75rem; }
	input[type=text], input[type=email] { width: 100%; padding: .4rem; box-sizing: border-box; }
	button { cursor: pointer; margin-top: 1rem; padding: .5rem 1.5rem; background: #222; color: #fff; border: 0; border-radius: .3rem; }
	.errors { background: #fee; border: 1px solid #c00; border-radius: .3rem; padding: .5rem 1rem; }
	.confirmation { background: #efe; border: 1px solid #0a0; border-radius: .3rem; padding: .5rem 1rem; }
</style>
</head>
<body>

<header>
	<h1>🧾 Checkout</h1>
	<div>⭐ balance: <?= getStarBalance( $cart ) ?></div>
</header>

<?php if( $placedOrder ): ?>

<div class="confirmation">
	<h2>Thanks, <?= htmlspecialchars( $placedOrder['name'] ) ?>! 🎉</h2>
	<p>Order <strong><?= htmlspecialchars( $placedOrder['number'] ) ?></strong> is confirmed.
	A receipt is on its way to <?= htmlspecialchars( $placedOrder['email'] ) ?>.</p>
</div>

<table>
	<thead>
		<tr><th>Product</th><th class="num">Qty</th><th class="num">Line Total</th><th class="num">Stars</th></tr>
	</thead>
	<tbody>
		<?php foreach( $placedOrder['lines'] as $line ): ?>
		<tr>
			<td><?= htmlspecialchars( $line['name'] ) ?></td>
			<td class="num"><?= (int)$line['qty'] ?></td>
			<td class="num"><?= $line['lineTotal'] ?></td>
			<td class="num"><?= $line['stars'] ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot>
		<tr>
			<td>Paid</td>
			<td></td>
			<td class="num"><?= $placedOrder['total'] ?></td>
			<td class="num"><?= $placedOrder['starCost'] ?></td>
		</tr>
	</tfoot>
</table>

<p><a href="index.php">← Keep shopping</a></p>

<?php elseif( ! $cart->getCartItems() ): ?>

<p>Your cart is empty — nothing to check out.</p>
<p><a href="index.php">← Back to the store</a></p>

<?php else: ?>

<?php if( $errors ): ?>
<div class="errors">
	<ul>
		<?php foreach( $errors as $error ): ?>
		<li><?= htmlspecialchars( $error ) ?></li>
		<?php endforeach ?>
	</ul>
</div>
<?php endif ?>

<h2>Order Summary</h2>
<table>
	<thead>
		<tr><th>Product</th><th class="num">Qty</th><th class="num">Line Total</th><th class="num">Stars</th></tr>
	</thead>
	<tbody>
		<?php foreach( $cart->getCartItems() as $item ): ?>
		<tr>
			<td><?= $item->getProduct()->getEmoji() ?> <?= htmlspecialchars( $item->getProduct()->getName() ) ?></td>
			<td class="num"><?= $item->getQty() ?></td>
			<td class="num"><?= $item->formatAmount( productAmountFormatterPrice::tPRICE ) ?></td>
			<td class="num"><?= $starsTotal->format( $item->getTotalAmount( $starsTotal ) ) ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
	<tfoot>
		<tr>
			<td>Due</td>
			<td class="num"><?= $cart->getTotalQty() ?></td>
			<td class="num"><?= $cart->getTotalFormatted( $priceTotal ) ?></td>
			<td class="num"><?= $starsTotal->format( $starCost ) ?></td>
		</tr>
	</tfoot>
</table>

<?php if( bccomp( $shortfall, '0' ) > 0 ): ?>
	<p>⚠️ You need <?= $shortfall ?> more ⭐ — <a href="index.php">deposit them in the store</a> before checking out.</p>
<?php endif ?>

<h2>Your Details</h2>
<form method="post">
	<label>Name
		<input type="text" name="name" value="<?= htmlspecialchars( $_POST['name'] ?? '' ) ?>" required>
	</label>
	<label>Email
		<input type="email" name="email" value="<?= htmlspecialchars( $_POST['email'] ?? '' ) ?>" required>
	</label>
	<button type="submit">Place order (dollars on delivery, stars now)</button>
</form>

<p><a href="index.php">← Back to the store</a></p>

<?php endif ?>

</body>
</html>

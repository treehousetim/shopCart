<?php

use treehousetim\shopCart\productAmountFormatterPrice;

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/gateway.php';

$starCost = $cart->getAmountTotal( $starsTotal );
$balance = getStarBalance( $cart );
$shortfall = bcsub( $starCost, $balance, 0 );
$errors = [];
$cancelled = false;
$gateway = paymentGateway();

// Stripe requires absolute success/cancel URLs, so build one for this page
$checkoutUrl = ( ( $_SERVER['HTTPS'] ?? '' ) === 'on' ? 'https' : 'http' )
	. '://' . $_SERVER['HTTP_HOST']
	. strtok( $_SERVER['REQUEST_URI'], '?' );

// the checkout form fields; street2 is the only optional one
$fields = [ 'name' => '', 'email' => '', 'street1' => '', 'street2' => '', 'city' => '', 'state' => '', 'postal' => '', 'country' => '' ];

foreach( $fields as $field => $unused )
{
	$fields[$field] = trim( $_POST[$field] ?? '' );
}

// ---------------------------------------------------------------- start payment (post/redirect/get)
if( $_SERVER['REQUEST_METHOD'] === 'POST' )
{
	if( ! $cart->getCartItems() )
	{
		$errors[] = 'Your cart is empty.';
	}

	if( $fields['name'] === '' )
	{
		$errors[] = 'Name is required.';
	}

	if( ! filter_var( $fields['email'], FILTER_VALIDATE_EMAIL ) )
	{
		$errors[] = 'A valid email is required.';
	}

	if( $fields['street1'] === '' )
	{
		$errors[] = 'Street address is required.';
	}

	if( $fields['city'] === '' )
	{
		$errors[] = 'City is required.';
	}

	if( $fields['state'] === '' )
	{
		$errors[] = 'State / province is required.';
	}

	if( $fields['postal'] === '' )
	{
		$errors[] = 'Postal code is required.';
	}

	if( $fields['country'] === '' )
	{
		$errors[] = 'Country is required.';
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

		// Stage the order in the session but charge NOTHING yet.  With a real
		// gateway the customer leaves this site to pay; the stars are deducted
		// and the cart emptied only when they come back to ?placed=1.  The
		// "order" lives in the session only — this example has no backend.
		$_SESSION['pending_order'] = [
			'number' => 'CC-' . strtoupper( bin2hex( random_bytes( 4 ) ) ),
			'name' => $fields['name'],
			'email' => $fields['email'],
			'address' => [
				'street1' => $fields['street1'],
				'street2' => $fields['street2'],
				'city' => $fields['city'],
				'state' => $fields['state'],
				'postal' => $fields['postal'],
				'country' => $fields['country'],
			],
			'lines' => $lines,
			'total' => $cart->getTotalFormatted( $priceTotal ),
			'starCost' => $starsTotal->format( $starCost ),
			'starCostRaw' => $starCost,
			'gateway' => $gateway->getName(),
		];

		try
		{
			// testGateway sends us straight to ?placed=1; stripeGateway sends
			// the customer to Stripe's hosted payment page first
			$redirectUrl = $gateway->createPayment(
				$cart,
				$_SESSION['pending_order'],
				$checkoutUrl . '?placed=1',
				$checkoutUrl . '?cancelled=1'
			);

			header( 'Location: ' . $redirectUrl );
			exit;
		}
		catch( Exception $e )
		{
			unset( $_SESSION['pending_order'] );
			$errors[] = 'Payment could not be started: ' . $e->getMessage();
		}
	}
}

// ---------------------------------------------------------------- payment outcome
$placedOrder = null;

if( isset( $_GET['placed'] ) )
{
	if( isset( $_SESSION['pending_order'] ) )
	{
		// the gateway reported success — NOW pay the stars and empty the cart
		// (emptyCart() clears cart data, so the remaining balance is re-added)
		$pending = $_SESSION['pending_order'];

		$newBalance = bcsub( getStarBalance( $cart ), $pending['starCostRaw'], 0 );
		$cart->emptyCart();
		$cart->addData( ( new starBalance() )->setData( $newBalance ) );
		$cart->save();

		$_SESSION['last_order'] = $pending;
		unset( $_SESSION['pending_order'] );
	}

	if( isset( $_SESSION['last_order'] ) )
	{
		$placedOrder = $_SESSION['last_order'];
	}
}
elseif( isset( $_GET['cancelled'] ) )
{
	// the customer backed out at the gateway: no order, no star charge,
	// and the cart is left exactly as it was
	unset( $_SESSION['pending_order'] );
	$cancelled = true;
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
	.row { display: flex; gap: 1rem; }
	.row label { flex: 1; }
	button { cursor: pointer; margin-top: 1rem; padding: .5rem 1.5rem; background: #222; color: #fff; border: 0; border-radius: .3rem; }
	.errors { background: #fee; border: 1px solid #c00; border-radius: .3rem; padding: .5rem 1rem; }
	.confirmation { background: #efe; border: 1px solid #0a0; border-radius: .3rem; padding: .5rem 1rem; }
	.notice { background: #ffd; border: 1px solid #ca0; border-radius: .3rem; padding: .5rem 1rem; }
	.gateway { color: #666; font-size: .9rem; }
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
	<?php if( ! empty( $placedOrder['address'] ) ): $address = $placedOrder['address']; ?>
	<p>Shipping to:<br>
		<?= htmlspecialchars( $address['street1'] ) ?><br>
		<?php if( $address['street2'] !== '' ): ?><?= htmlspecialchars( $address['street2'] ) ?><br><?php endif ?>
		<?= htmlspecialchars( $address['city'] ) ?>, <?= htmlspecialchars( $address['state'] ) ?> <?= htmlspecialchars( $address['postal'] ) ?><br>
		<?= htmlspecialchars( $address['country'] ) ?>
	</p>
	<?php endif ?>
	<p class="gateway">Payment handled by <?= htmlspecialchars( $placedOrder['gateway'] ?? 'unknown gateway' ) ?>.</p>
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

<?php if( $cancelled ): ?>
<div class="notice">
	<p>Payment cancelled — no worries, nothing was charged and no ⭐ were spent.</p>
</div>
<?php endif ?>

<p>Your cart is empty — nothing to check out.</p>
<p><a href="index.php">← Back to the store</a></p>

<?php else: ?>

<?php if( $cancelled ): ?>
<div class="notice">
	<p>Payment cancelled — no worries, nothing was charged and no ⭐ were spent.
	Your cart is still intact below if you'd like to try again.</p>
</div>
<?php endif ?>

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
		<input type="text" name="name" value="<?= htmlspecialchars( $fields['name'] ) ?>" required>
	</label>
	<label>Email
		<input type="email" name="email" value="<?= htmlspecialchars( $fields['email'] ) ?>" required>
	</label>

	<h2>Shipping Address</h2>
	<label>Street Address
		<input type="text" name="street1" value="<?= htmlspecialchars( $fields['street1'] ) ?>" required>
	</label>
	<label>Street Address 2 <small>(optional)</small>
		<input type="text" name="street2" value="<?= htmlspecialchars( $fields['street2'] ) ?>">
	</label>
	<div class="row">
		<label>City
			<input type="text" name="city" value="<?= htmlspecialchars( $fields['city'] ) ?>" required>
		</label>
		<label>State / Province
			<input type="text" name="state" value="<?= htmlspecialchars( $fields['state'] ) ?>" required>
		</label>
	</div>
	<div class="row">
		<label>Postal Code
			<input type="text" name="postal" value="<?= htmlspecialchars( $fields['postal'] ) ?>" required>
		</label>
		<label>Country
			<input type="text" name="country" value="<?= htmlspecialchars( $fields['country'] ) ?>" required>
		</label>
	</div>

	<button type="submit">Pay <?= $cart->getTotalFormatted( $priceTotal ) ?> + <?= $starsTotal->format( $starCost ) ?></button>
	<p class="gateway">Payments handled by <?= htmlspecialchars( $gateway->getName() ) ?>.</p>
</form>

<p><a href="index.php">← Back to the store</a></p>

<?php endif ?>

</body>
</html>

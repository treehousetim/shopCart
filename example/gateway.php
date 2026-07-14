<?php

use treehousetim\shopCart\cart;

// ---------------------------------------------------------------- payment gateways
// A minimal payment abstraction so the example checkout can swap between a
// simulated gateway and real Stripe Checkout without touching checkout.php.
//
// No composer dependency: stripeGateway talks to Stripe's REST API with raw
// curl.  The comments inside createPayment() mark exactly where calls to the
// stripe/stripe-php SDK would replace the raw curl if you add the SDK.

interface paymentGatewayInterface
{
	// human-readable name, shown on the confirmation page
	public function getName() : string;

	// Start a payment for the staged order and return the URL the customer
	// should be redirected to.  An instant gateway returns $successUrl
	// directly; a hosted gateway (Stripe Checkout) returns its own payment
	// page, which later sends the customer back to $successUrl or $cancelUrl.
	public function createPayment( cart $cart, array $order, string $successUrl, string $cancelUrl ) : string;
}

// ---------------------------------------------------------------- testGateway
// The out-of-the-box default: every payment succeeds instantly, so the
// example works without any Stripe account or API key.
class testGateway implements paymentGatewayInterface
{
	public function getName() : string
	{
		return 'Test Gateway (simulated payment)';
	}
	//------------------------------------------------------------------------
	public function createPayment( cart $cart, array $order, string $successUrl, string $cancelUrl ) : string
	{
		// a real gateway would collect payment here / at a hosted page;
		// the test gateway simply pretends it already succeeded
		return $successUrl;
	}
}

// ---------------------------------------------------------------- stripeGateway
// Creates a genuine Stripe Checkout Session and redirects the customer to
// Stripe's hosted payment page.  Selected automatically when the
// STRIPE_SECRET_KEY environment variable is set.
class stripeGateway implements paymentGatewayInterface
{
	protected $secretKey = '';

	public function __construct( string $secretKey )
	{
		$this->secretKey = $secretKey;
	}
	//------------------------------------------------------------------------
	public function getName() : string
	{
		return 'Stripe Checkout';
	}
	//------------------------------------------------------------------------
	// Builds the exact parameter array a Stripe Checkout Session expects.
	// With the stripe/stripe-php SDK, this array would be passed unchanged to
	// \Stripe\Checkout\Session::create( $payload ).
	public function buildSessionPayload( cart $cart, array $order, string $successUrl, string $cancelUrl ) : array
	{
		$lineItems = [];

		foreach( $cart->getCartItems() as $item )
		{
			$product = $item->getProduct();

			$lineItems[] = [
				'quantity' => (int)$item->getQty(),
				'price_data' => [
					'currency' => 'usd',
					// Stripe wants integer cents; cart prices are bcmath strings
					'unit_amount' => (int)bcmul( $product->getPrice(), '100', 0 ),
					'product_data' => [
						'name' => $product->getEmoji() . ' ' . $product->getName(),
					],
				],
			];
		}

		return [
			'mode' => 'payment',
			'line_items' => $lineItems,
			'success_url' => $successUrl,
			'cancel_url' => $cancelUrl,
			'customer_email' => $order['email'],
			// metadata comes back on the webhook / session retrieve, letting a
			// real backend reconcile the Stripe payment with this order
			'metadata' => [
				'order_number' => $order['number'],
				'star_cost' => $order['starCostRaw'],
			],
		];
	}
	//------------------------------------------------------------------------
	public function createPayment( cart $cart, array $order, string $successUrl, string $cancelUrl ) : string
	{
		$payload = $this->buildSessionPayload( $cart, $order, $successUrl, $cancelUrl );

		// --- with the stripe/stripe-php SDK, everything below becomes: ------
		// \Stripe\Stripe::setApiKey( $this->secretKey );
		// $session = \Stripe\Checkout\Session::create( $payload );
		// return $session->url;
		// ---------------------------------------------------------------------

		// Raw curl instead.  Stripe's API takes form-encoded bodies with
		// bracket-array keys, e.g. line_items[0][price_data][unit_amount]=1999;
		// http_build_query() produces exactly that shape (brackets urlencoded,
		// which Stripe accepts).
		$ch = curl_init( 'https://api.stripe.com/v1/checkout/sessions' );

		curl_setopt_array( $ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => http_build_query( $payload ),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				'Authorization: Bearer ' . $this->secretKey,
				'Content-Type: application/x-www-form-urlencoded',
			],
		] );

		$body = curl_exec( $ch );
		$status = curl_getinfo( $ch, CURLINFO_RESPONSE_CODE );
		$curlError = curl_error( $ch );
		curl_close( $ch );

		if( $body === false )
		{
			throw new Exception( 'Could not reach Stripe: ' . $curlError );
		}

		$session = json_decode( $body, true );

		if( $status !== 200 )
		{
			$message = $session['error']['message'] ?? 'HTTP ' . $status;
			throw new Exception( 'Stripe rejected the payment request: ' . $message );
		}

		if( empty( $session['url'] ) )
		{
			throw new Exception( 'Stripe did not return a Checkout Session url.' );
		}

		// Stripe's hosted payment page; the customer lands back on
		// $successUrl or $cancelUrl when they finish or back out
		return $session['url'];
	}
}

// ---------------------------------------------------------------- factory
// Picks the gateway: real Stripe when STRIPE_SECRET_KEY is set in the
// environment, the simulated gateway otherwise.
function paymentGateway() : paymentGatewayInterface
{
	$secretKey = getenv( 'STRIPE_SECRET_KEY' );

	if( $secretKey )
	{
		return new stripeGateway( $secretKey );
	}

	return new testGateway();
}

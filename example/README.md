# Critter Supply Co. — shopCart example

A small pet supplies store showing the library end to end: a static catalog
of 25 products across seven categories (Food, Toys, Beds, Leashes & Collars,
Bowls & Feeders, Grooming, and Habitats) for dogs, cats, parrots, and bears,
session-backed cart storage, add/update/remove/empty actions, category and
text-search filtering, a dual-currency catalog (dollars and ⭐), a depositable
star balance carried as cart data, and a Stripe-ready checkout that
validates contact + shipping details, charges the star balance, and confirms
with an order number.

- `bootstrap.php` — shared wiring: product/formatter/cart-data subclasses,
  total types, catalog, and cart
- `index.php` — the storefront and cart: category filter bar, text search
  (both plain GET params, composable), product grid, and the cart table
- `checkout.php` — order summary, contact + shipping form with server-side
  validation, payment kickoff, and confirmation (the "order" is stored in
  the session only; there is no backend)
- `gateway.php` — the payment layer: `paymentGatewayInterface` plus two
  implementations, `testGateway` and `stripeGateway`

This directory is `export-ignore`d, so it never ships in the composer dist
package — it only exists in the git repo.

## Run it

From this `example/` directory:

```sh
docker compose run --rm composer   # one-time dependency install
docker compose up --build          # serve the store
```

Then open <http://localhost:8000>.

The `store` service builds a small image from the `Dockerfile` here because
the library needs `ext-bcmath` for money math and official `php` images
don't bundle it. The `composer` service passes `--ignore-platform-req=php`
until `treehousetim/exception` tags a release that allows PHP 8.

## What it demonstrates

- Subclassing the abstract `product` (`petSupplyProduct` adds emoji and
  stars; categories use the parent's `setCategory`/`getCategory`)
- A second cost dimension via `catalogTotalType::tPRODUCT_FIELD` — products
  cost dollars *and* stars, each summed and formatted as its own total
- Custom total formatting (`starsTotalFormatter` + the library's
  `formatting::unitFormatAutoScale`)
- A session-persisted star balance via `cartData`/`addData()` — note the
  class is defined before `session_start()` so PHP can unserialize it
- `load()`/`save()` round-tripping the cart through `$_SESSION` across
  requests, with post/redirect/get for the form actions

## Checkout & payments

Placing an order is a two-phase flow built to survive a redirect to a hosted
payment page:

1. **POST** — the form (name, email, shipping address) is validated, the
   star balance is checked against the cart's ⭐ total, and the order
   (number, customer, address, line items, totals, gateway) is staged in
   `$_SESSION['pending_order']`. Nothing is charged yet. The customer is
   then redirected wherever the gateway says.
2. **Return** — landing on `checkout.php?placed=1` promotes the pending
   order to `$_SESSION['last_order']`, deducts the stars, and empties the
   cart (re-adding the remaining balance, since `emptyCart()` clears cart
   data). Landing on `?cancelled=1` discards the pending order and leaves
   the cart and stars untouched.

`gateway.php` defines `paymentGatewayInterface` — `getName()` and
`createPayment( cart, order, successUrl, cancelUrl )`, which returns the URL
to send the customer to — with two implementations:

- **`testGateway`** (the default): pretends payment succeeded instantly and
  returns the success URL, so the example works out of the box with no
  Stripe account.
- **`stripeGateway`**: builds a real Stripe Checkout Session payload from
  the cart — `line_items` with `price_data` (`currency: usd`, integer-cent
  `unit_amount` via `bcmul( price, '100', 0 )`, `product_data.name`),
  `mode: payment`, success/cancel URLs pointing back at `checkout.php`, and
  `metadata` carrying the order number and star cost — then POSTs it to
  `https://api.stripe.com/v1/checkout/sessions` with raw curl (form-encoded
  bracket arrays, Bearer auth) and redirects the customer to the returned
  hosted payment page. No composer dependency is needed; comments in
  `createPayment()` mark where `stripe/stripe-php` SDK calls would replace
  the raw curl.

### Switching on live Stripe

Set the `STRIPE_SECRET_KEY` environment variable for the `store` service
(e.g. in `docker-compose.yml` or an `.env` file) to your `sk_test_...` or
`sk_live_...` key and restart. The factory `paymentGateway()` picks
`stripeGateway` automatically whenever the variable is set; unset it to fall
back to the simulated gateway. Note the success redirect is treated as proof
of payment for demo purposes — a production integration should confirm via a
`checkout.session.completed` webhook (the session `metadata` already carries
the order number for reconciliation).

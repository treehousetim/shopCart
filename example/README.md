# Critter Chow — shopCart example

A minimal one-file pet food store showing the library end to end: a static
catalog (dog, cat, parrot, and bear food), session-backed cart storage,
add/update/remove/empty actions, a dual-currency catalog (dollars and ⭐),
and a depositable star balance carried as cart data.

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

- Subclassing the abstract `product` (`petFoodProduct` adds emoji and stars)
- A second cost dimension via `catalogTotalType::tPRODUCT_FIELD` — products
  cost dollars *and* stars, each summed and formatted as its own total
- Custom total formatting (`starsTotalFormatter` + the library's
  `formatting::unitFormatAutoScale`)
- A session-persisted star balance via `cartData`/`addData()` — note the
  class is defined before `session_start()` so PHP can unserialize it
- `load()`/`save()` round-tripping the cart through `$_SESSION` across
  requests, with post/redirect/get for the form actions

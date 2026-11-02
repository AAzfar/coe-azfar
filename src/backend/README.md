# Backend — Laravel REST API

The FreshKeep Market API: Laravel 13 on PHP 8.4, PostgreSQL 15. It is started by the root `docker-compose.yml`. See the [root README](../../README.md) for setup.

Base URL: `http://localhost:8000/api`. The frontend reaches it at `http://localhost:3000/api` through its proxy.

## Layout

| Path                                   | What's there                                           |
|----------------------------------------|--------------------------------------------------------|
| `routes/api.php`                       | All API routes                                         |
| `app/Http/Controllers/`                | `ProductController`, `CategoryController`, `OrderController`; shared list helpers in `Controller` |
| `app/Http/Resources/`                  | JSON shape of each model                               |
| `app/Exceptions/ApiExceptionRenderer.php` | The single error format for every `/api` response   |
| `app/Models/`                          | `Product`, `Category`, `Order`, `OrderItem`            |
| `database/migrations/`                 | Schema                                                 |
| `database/seeders/`                    | Sample categories and groceries (safe to re-run)       |
| `tests/Feature/`                       | API, error-format, migration and seeder tests          |

## Schema

```
categories 1 ──< products 1 ──< order_items >── 1 orders
```

| Table         | Key columns | Constraints and indexes |
|---------------|-------------|-------------------------|
| `categories`  | `name`, `slug`, `is_active` | `slug` unique |
| `products`    | `name`, `sku`, `category_id`, `price`, `stock`, `unit`, `image_url` | `sku` unique; FK `category_id → categories` (restrict delete); indexes on `category_id`, `name`, `price`, `stock`, `created_at` |
| `orders`      | `customer_name`, `customer_email`, `status`, `total` | indexes on `customer_email`, `status`, `created_at` |
| `order_items` | `order_id`, `product_id`, `quantity`, `unit_price` | FK `order_id → orders` (cascade delete); FK `product_id → products` (restrict delete); unique (`order_id`, `product_id`); index on `product_id` |

`products.stock` is the inventory quantity. Placing an order decrements it inside a transaction. `order_items.unit_price` keeps the price paid, so later price changes don't rewrite past orders.

## Endpoints

| Method          | Path                    | Notes |
|-----------------|-------------------------|-------|
| GET             | `/api/products`         | List (see query parameters below) |
| POST            | `/api/products`         | Create. Body: `name`, `sku`, `category_id`, `price`, `stock`, optional `unit`, `image_url` |
| GET             | `/api/products/{id}`    | One product, with its category |
| PUT / PATCH     | `/api/products/{id}`    | Update any subset of the create fields |
| DELETE          | `/api/products/{id}`    | `204`; `409` if the product is on an order |
| GET             | `/api/categories`       | List, with `products_count` |
| GET             | `/api/categories/{id}`  | One category |
| GET             | `/api/orders`           | List, with `items_count` |
| POST            | `/api/orders`           | Body: `customer_name`, `customer_email`, `items: [{product_id, quantity}]`. `422` if any item exceeds stock; nothing is changed in that case |
| GET             | `/api/orders/{id}`      | One order, with items and products |

### List query parameters

All list endpoints accept:

| Param      | Example          | Meaning |
|------------|------------------|---------|
| `page`     | `2`              | Page number (default 1) |
| `per_page` | `20`             | Page size, 1–100 (default 15) |
| `search`   | `apple`          | Case-insensitive substring match. Products: name, SKU or category name. Categories: name. Orders: customer name or email |
| `sort`     | `price`, `-price`| Sort field; a leading `-` sorts descending |

Endpoint-specific filters and sort fields:

| Endpoint          | Filters | Sort fields (default) |
|-------------------|---------|-----------------------|
| `/api/products`   | `category` (slug), `category_id`, `min_price`, `max_price`, `in_stock` (`1` or `0`) | `name`, `sku`, `price`, `stock`, `created_at` (`name`) |
| `/api/categories` | `is_active` (`1` or `0`) | `name`, `slug`, `created_at` (`name`) |
| `/api/orders`     | `status` (`pending`, `paid`, `shipped`, `cancelled`) | `created_at`, `total`, `status` (`-created_at`) |

Unknown sort fields and out-of-range values return a `422`.

Example: `GET /api/products?category=bakery&in_stock=1&sort=-price&per_page=5`

### Response shape

Single records come back as `{ "data": { ... } }`. Lists are paginated:

```json
{
  "data": [ { "id": 1, "name": "Fuji Apples", "category": { "id": 1, "name": "Fresh Foods", "slug": "fresh-foods" }, "price": "8.90", "stock": 42 } ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 2, "per_page": 15, "total": 18, "from": 1, "to": 15 }
}
```

Prices and totals are decimal strings (`"8.90"`) so no precision is lost.

### Errors

Every error from `/api`, whatever its cause, has the same shape and never includes a stack trace:

```json
{
  "error": {
    "status": 422,
    "code": "validation_failed",
    "message": "The sku has already been taken.",
    "details": { "sku": ["The sku has already been taken."] }
  }
}
```

| Status | `code`               | When |
|--------|----------------------|------|
| 404    | `not_found`          | Unknown record (`"Product not found."`) or route |
| 405    | `method_not_allowed` | Wrong HTTP method |
| 409    | `conflict`           | E.g. deleting a product that has orders |
| 422    | `validation_failed`  | Invalid body or query; `details` maps each field to its messages |
| 500    | `server_error`       | Anything unexpected. The real exception goes to `storage/logs/laravel.log` |

`details` is only present on `422` responses.

## Common commands

Run these from the repo root:

```bash
docker compose exec backend php artisan test --compact     # tests (in-memory SQLite, never the dev DB)
docker compose exec backend php artisan migrate             # apply new migrations
docker compose exec backend php artisan db:seed             # add any missing sample data
docker compose exec backend php artisan route:list --path=api
docker compose exec backend vendor/bin/pint                 # format PHP
```

## Configuration

`.env.example` holds working defaults for Docker. The container copies it to `.env` on first start and generates `APP_KEY`. `docker-compose.yml` overrides a few values for local development (`DB_HOST`, session, cache and queue drivers).
